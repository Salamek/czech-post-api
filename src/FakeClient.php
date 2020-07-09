<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi;


use Salamek\HttpRequest;
use Salamek\HttpResponse;

class FakeClient
{
    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var HttpRequest */
    private $httpRequest;

    /** @var string */
    public $importConfiguration;

    /** @var bool  */
    private $initialized = false;

    private $loginFormActionUrl = 'https://www.postaonline.cz/rap/prihlaseni/-/login/password';
    private $filingsOnlineUrl = 'https://www.postaonline.cz/podanionline/';
    private $filingsOnlineImportActionUrlForm = 'https://www.postaonline.cz/podanionline/ZasilkyImportSeznam.action';
    private $filingsOnlineImportActionUrl = 'https://www.postaonline.cz/podanionline/ZasilkyImportDoNoveDavkyIzDialog.action';
    private $newFillingActionUrl = 'https://www.postaonline.cz/podanionline/rucVstupZasilka.action';
    private $activeFillingsListUrl = 'https://www.postaonline.cz/podanionline/rucVstupZasilka!prehledPodaniRucniVstup.action';

    const IMPORT_FORMAT_EXPORTED = '-4;/Import exportovaných dat - zásilky;/false';
    const IMPORT_FORMAT_DEFAULT = '2;/Přednastavená konfigurace zásilek;/false';

    /**
     * CzechPostApi constructor.
     * @param $username
     * @param $password
     * @param null $cookieJar
     * @param string $importConfiguration
     */
    public function __construct(
        $username,
        $password,
        $cookieJar = null,
        $importConfiguration = self::IMPORT_FORMAT_EXPORTED
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->importConfiguration = $importConfiguration;

        if (is_null($cookieJar)) {
            $cookieJar = tempnam(sys_get_temp_dir(), 'cookiejar.txt');
        }

        $this->httpRequest = new HttpRequest($cookieJar);
    }

    /**
     * @throws \Exception
     */
    public function neededConnection()
    {
        if (!$this->isLoggedIn()) {
            $this->login($this->username, $this->password);
        }

        if (!$this->initialized) {
            $this->loadFilingsOnlineApp();
            $this->initialized = true;
        }
    }

    /**
     * Form processing
     * @param $pageUrl
     * @param $id
     * @param array $postData
     * @return HttpResponse
     * @throws \Exception
     */
    private function form($pageUrl, $id, $postData = [], $ignoreHiddenOverrides = [])
    {
        $httpResponse = $this->httpRequest->get($pageUrl);
        //Find needed data from signIn page as form action and hidden fields
        $xpath = $httpResponse->getBody(HttpResponse::FORMAT_HTML);
        $form = $xpath->query('//*[@id="' . $id . '"]');
        $hiddens = $xpath->query('//*[@id="' . $id . '"]//input[@type=\'hidden\']');
        if (!$form->item(0)) {
            throw new \Exception('Failed to load ' . $id . ' form');
        }

        foreach ($hiddens AS $hidden) {
            if (in_array($hidden->getAttribute('name'), $ignoreHiddenOverrides)) continue;

            $postData[$hidden->getAttribute('name')] = $hidden->getAttribute('value');
        }

        //Find p_auth
        // CP uses p_auth as some kind of protection
        $matches = [];
        $action = $form->item(0)->getAttribute('action');
        if (preg_match('/p_auth=(\S{8})/i', $httpResponse->getRawBody(), $matches)) {
            if (strpos($action, '?') !== false) {
                $action .= '&p_auth=' . $matches[1];
            } else {
                $action .= '?p_auth=' . $matches[1];
            }

        }
        $url = HttpRequest::absolutizeHtmlUrl($httpResponse->getLastUrl(), $action);

        return $this->httpRequest->post($url, $postData);
    }

    /**
     * Checks if user is logged in
     * @return bool
     */
    private function isLoggedIn()
    {
        $httpResponse = $this->httpRequest->get($this->loginFormActionUrl);
        return (strpos($httpResponse->getRawBody(), 'Přihlášení') === false);
    }

    /**
     * Log in user
     * @param $username
     * @param $password
     * @throws \Exception
     */
    private function login($username, $password)
    {
        $id = 'loginForm';
        $postData = [];
        $postData['username'] = $username;
        $postData['password'] = $password;
        $httpResponse = $this->form($this->loginFormActionUrl, $id, $postData);

        $xpath = $httpResponse->getBody(HttpResponse::FORMAT_HTML);
        $warnings = $xpath->query('//form[@id="' . $id . '"]//div[contains(@class, \'warning\')]//div[contains(@class, \'error\')]');

        if ($warnings->length) {
            foreach ($warnings AS $childNode) {
                throw new \Exception(sprintf('Failed to login: %s', $childNode->textContent));
            }
        }
    }

    /**
     * Loads Czech Post Filings Online App
     * @throws \Exception
     */
    private function loadFilingsOnlineApp()
    {
        $httpResponse = $this->httpRequest->get($this->filingsOnlineUrl);
        if (strpos($httpResponse->getRawBody(), 'Seznam podání') === false) {
            throw new \Exception('Failed to load filingsOnline app');
        }
    }

    /**
     * Creates new filing
     * @param $techId
     * @param \DateTimeInterface $filingDate
     * @param $number
     * @param string $orderNumber
     * @param string $numberVs
     * @throws \Exception
     */
    public function createFiling($techId, \DateTimeInterface $filingDate, $number, $orderNumber = '', $numberVs = '', $cardIdentifier = 'null;/1;/0;/null')
    {
        $this->neededConnection();
        $postData = [];
        $postData['idTnt'] = $techId;
        $postData['datumPodani'] = $filingDate->format('d.m.Y');
        $postData['cisloPodani'] = $number;
        $postData['cisloZakazky'] = $orderNumber;
        $postData['cisloVS'] = $numberVs;
        $postData['zakaznickaKartaSelectValue'] = $cardIdentifier;
        $postData['action:rucVstupZasilkaUlozPodani'] = 'Uložit';
        $ignoredHiddenOverrides = ['datumPodani']; //// Ignored default value set by the website
        $httpResponse = $this->form($this->newFillingActionUrl, 'rucVstupZasilka', $postData, $ignoredHiddenOverrides);

        if (strpos($httpResponse->getRawBody(), 'errorMessages') !== false) {
            throw new \Exception('Failed to create filling');
        }
    }

    /**
     * Imports shipments into open filing
     * @param array $importBatch
     * @return array
     * @throws \Exception
     */
    public function importBatchData(array $importBatch)
    {
        $this->neededConnection();

        if (empty($this->getOpenFilings())) {
            throw new \Exception('You have no open filling, call createFiling to open one');
        }

        $id = 'ZasilkyImport';
        $tmpfname = __DIR__."/import.csv";

        $fp = fopen($tmpfname, 'w');
        foreach ($importBatch as $fields) {
            switch($this->importConfiguration)
            {
                //IMPORT_FORMAT_EXPORTED accepts CP1250 as encoding
                case self::IMPORT_FORMAT_EXPORTED:
                    foreach($fields AS &$field)
                    {
                        $field = iconv('UTF-8', 'CP1250//TRANSLIT', $field);
                        $field = strip_tags(trim(preg_replace('/\s+/', ' ', $field)));
                    }

                    break;

                //IMPORT_FORMAT_DEFAULT uses CP852/DOS
                case self::IMPORT_FORMAT_DEFAULT:
                    foreach($fields AS &$field)
                    {
                        $field = iconv('UTF-8', 'CP852//TRANSLIT', $field);
                        $field = strip_tags(trim(preg_replace('/\s+/', ' ', $field)));
                    }
                    break;
            }

            fputcsv($fp, $fields, ';');
        }
        fclose($fp);

        $importName = sprintf('import-%s-%s.csv', 'CzechPostApi', (new \DateTime)->format('Y-m-d H:i:s'));

        $postData = [];
        $postData['bean.importTablePageSize'] = 10;
        $postData['bean.sortImportOrder'] = 1;
        $postData['bean.sortedImportColumn'] = '03_datum';

        $postData['idKonfigNazevIndVychozi'] = $this->importConfiguration;

        $postData['data'] = HttpRequest::createFile($tmpfname, 'text/csv', $importName);
        $postData['button.novyimport.CSV'] = 'Import CSV';

        $httpResponse = $this->httpRequest->get($this->filingsOnlineImportActionUrlForm);
        //Find needed data from signIn page as form action and hidden fields
        $xpath = $httpResponse->getBody(HttpResponse::FORMAT_HTML);
        $form = $xpath->query('//*[@id="' . $id . '"]');
        $hiddens = $xpath->query('//*[@id="' . $id . '"]//input[@type=\'hidden\']');
        if (!$form->item(0)) {
            throw new \Exception('Failed to load ' . $id . ' form');
        }

        /** @var \DOMElement $hidden */
        foreach ($hiddens AS $hidden) {
            $postData[$hidden->getAttribute('name')] = $hidden->getAttribute('value');
        }

        //This gets overwritten by hiddens, thats why its here
        $postData['druhImportu'] = 'CSV';

        $httpResponse = $this->httpRequest->post($this->filingsOnlineImportActionUrl, $postData);

        unlink($tmpfname);

        if (strpos($httpResponse->getRawBody(), 'successMessages') === false || strpos($httpResponse->getRawBody(), 'errorMessages') !== false) {
            throw new \Exception('Failed to import batch data');
        }

        return $httpResponse;
    }

    /**
     * Returns open filings
     * @return array
     * @throws \Exception
     */
    public function getOpenFilings()
    {
        $this->neededConnection();
        $id = 'podaniList';
        $httpResponse = $this->httpRequest->get($this->activeFillingsListUrl);
        $xpath = $httpResponse->getBody(HttpResponse::FORMAT_HTML);

        $filingTable = $xpath->query('//table[@id="' . $id . '"]');
        if (!$filingTable->item(0)) {
            throw new \Exception('Failed to load ' . $id . ' table');
        }

        $empty = $xpath->query('//table[@id="' . $id . '"]//tbody//tr[contains(@class, \'empty\')]');
        if ($empty->length == 1 && $empty->item(0)) {
            return [];
        } else {
            $trs = $xpath->query('//table[@id="' . $id . '"]//tbody//tr');
            if (!$trs->length) {
                throw new \Exception('Failed to load openFilings');
            }

            $return = [];


            /** @var \DOMElement $tr */
            foreach ($trs AS $tr) {

                $filingData = [
                    'id' => $tr->childNodes->item(3)->textContent,
                    'techId' => $tr->childNodes->item(5)->textContent,
                    'postId' => $tr->childNodes->item(7)->textContent,
                    'filingDate' => new \DateTime(implode('-', array_reverse(explode('.', $tr->childNodes->item(9)->textContent)))),
                    'numberOfShipments' => $tr->childNodes->item(17)->textContent,
                    'closeUrl' => null,
                    'deleteUrl' => null,
                    'blocked' => false
                ];

                $deleteNode18 = $tr->childNodes->item(18); // This part works on some servers
                if ($deleteNode18 != null && $deleteNode18->childNodes != null) {
                    $deleteNode = $deleteNode18->childNodes->item(3);
                } else {
                    $deleteNode17 = $tr->childNodes->item(17); // This part works on other
                    if ($deleteNode17 != null && $deleteNode17->childNodes != null) {
                        $deleteNode = $deleteNode17->childNodes->item(3);
                    }
                } // I am so sorry, but I don't know how to fix this in better way

                if ($deleteNode)
                {
                    switch ($deleteNode->getAttribute('id')) {
                        case 'uzavritURLId':
                            $filingData['closeUrl'] = ($deleteNode ? HttpRequest::absolutizeHtmlUrl($this->activeFillingsListUrl, $deleteNode->getAttribute('href')) : null);
                            break;

                        case 'zrusitURLId':
                            $filingData['deleteUrl'] = ($deleteNode ? HttpRequest::absolutizeHtmlUrl($this->activeFillingsListUrl, $deleteNode->getAttribute('href')) : null);
                            break;
                    }
                }
                else
                {
                    $filingData['blocked'] = true;
                }

                $return[] = $filingData;
            }

            return $return;
        }
    }

    /**
     * Returns open batches
     * @return array
     * @throws \Exception
     */
    public function getOpenBatches()
    {
        $this->neededConnection();
        $id = 'davkaList';
        $httpResponse = $this->httpRequest->get($this->activeFillingsListUrl);
        $xpath = $httpResponse->getBody(HttpResponse::FORMAT_HTML);
        $filingTable = $xpath->query('//table[@id="' . $id . '"]');
        if (!$filingTable->item(0)) {
            throw new \Exception('Failed to load ' . $id . ' table');
        }

        $empty = $xpath->query('//table[@id="' . $id . '"]//tbody//tr[contains(@class, \'empty\')]');
        if ($empty->length == 1 && $empty->item(0)) {
            return [];
        } else {
            $trs = $xpath->query('//table[@id="' . $id . '"]//tbody//tr');
            if (!$trs->length) {
                throw new \Exception('Failed to load openBatches');
            }

            $return = [];


            /** @var \DOMElement $tr */
            foreach ($trs AS $tr) {
                $closeNode = $tr->childNodes->item(16)->childNodes->item(1);
                $detailNode = $tr->childNodes->item(0)->childNodes->item(1);

                $batchData = [
                    'id' => $tr->childNodes->item(2)->nodeValue,
                    'techId' => $tr->childNodes->item(4)->nodeValue,
                    'date' => new \DateTime(implode('-', array_reverse(explode('.', $tr->childNodes->item(6)->nodeValue)))),
                    'filingId' => $tr->childNodes->item(8)->nodeValue,
                    'owner' => $tr->childNodes->item(10)->nodeValue,
                    'numberOfShipments' => $tr->childNodes->item(14)->nodeValue,
                    'detailUrl' => ($detailNode ? HttpRequest::absolutizeHtmlUrl($this->activeFillingsListUrl, $detailNode->getAttribute('href')) : null),
                    'closeUrl' => null,
                    'deleteUrl' => null,
                    'blocked' => false
                ];

                if ($closeNode)
                {
                    $href = $closeNode->getAttribute('href');
                    if (strpos($href, 'rucVstupZasilkaUzavritDavku.action') !== false) {
                        $batchData['closeUrl'] = ($closeNode ? HttpRequest::absolutizeHtmlUrl($this->activeFillingsListUrl, $closeNode->getAttribute('href')) : null);

                    }
                    else if (strpos($href, 'rucVstupZasilkaZrusitDavku.action') !== false) {
                        $batchData['deleteUrl'] = ($closeNode ? HttpRequest::absolutizeHtmlUrl($this->activeFillingsListUrl, $closeNode->getAttribute('href')) : null);
                    }
                }
                else
                {
                    $batchData['blocked'] = true;
                }


                $return[] = $batchData;
            }

            return $return;
        }
    }

    /**
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function getBatchShipments($id)
    {
        $this->neededConnection();
        $htmlId = 'zasilkaList';
        $openBatches = $this->getOpenBatches();
        $detailUrl = null;
        foreach ($openBatches AS $openBatch) {
            if ($openBatch['id'] == $id) {
                $detailUrl = $openBatch['detailUrl'];
                break;
            }
        }

        if (!$detailUrl) {
            throw new \Exception('Unable to find batch');
        }

        $httpResponse = $this->httpRequest->get($detailUrl);


        $xpath = $httpResponse->getBody(HttpResponse::FORMAT_HTML);
        $empty = $xpath->query('//table[@id="' . $htmlId . '"]//tbody//tr[contains(@class, \'empty\')]');
        if ($empty->length == 1 && $empty->item(0)) {
            return [];
        } else {
            $trs = $xpath->query('//table[@id="' . $htmlId . '"]//tbody//tr');
            if (!$trs->length) {
                throw new \Exception('Failed to load barch shipments');
            }

            $return = [];

            foreach ($trs AS $tr) {
                $packageInfo = [
                    'id' => $tr->childNodes->item(18)->nodeValue,
                    'type' => $tr->childNodes->item(4)->nodeValue,
                    'weight' => $tr->childNodes->item(8)->nodeValue,
                    'goodsPrice' => $tr->childNodes->item(10)->nodeValue,
                    'cashOnDeliveryPrice' => trim($tr->childNodes->item(12)->nodeValue),
                    'vk' => $tr->childNodes->item(20)->nodeValue,
                    'retailPostage' => $tr->childNodes->item(22)->nodeValue,
                    'vkTotalWeight' => $tr->childNodes->item(24)->nodeValue,
                    'vkRetailPostage' => $tr->childNodes->item(26)->nodeValue,
                ];

                $packageInfo['address'] = trim(strpos($tr->childNodes->item(6)->nodeValue, '...') === false ? $tr->childNodes->item(6)->nodeValue : $tr->childNodes->item(6)->getAttribute('title'));
                $packageInfo['vsWarrant'] = (strpos($tr->childNodes->item(14)->nodeValue, '...') === false ? $tr->childNodes->item(14)->nodeValue : $tr->childNodes->item(14)->getAttribute('title'));
                $packageInfo['vsConsignment'] = (strpos($tr->childNodes->item(16)->nodeValue,
                    '...') === false ? $tr->childNodes->item(16)->nodeValue : $tr->childNodes->item(16)->getAttribute('title'));
                $packageInfo['services'] = explode(',',
                    trim(strpos($tr->childNodes->item(28)->nodeValue, '...') === false ? $tr->childNodes->item(28)->nodeValue : $tr->childNodes->item(28)->getAttribute('title')));

                $return[] = $packageInfo;
            }

            return $return;
        }
    }

    /**
     * Closes Batch by ID
     * @param $id
     * @throws \Exception
     */
    public function closeOpenBatch($id)
    {
        $this->neededConnection();
        $openBatches = $this->getOpenBatches();
        $closeUrl = null;
        foreach ($openBatches AS $openBatch) {
            if ($openBatch['id'] == $id) {
                $closeUrl = $openBatch['closeUrl'];
                break;
            }
        }

        if (!$closeUrl) {
            throw new \Exception('Unable to find batch');
        }

        $this->httpRequest->get($closeUrl);

        $openBatches = $this->getOpenBatches();
        $closed = true;
        foreach ($openBatches AS $openBatch) {
            if ($openBatch['id'] == $id && !is_null($openBatch['closeUrl'])) {
                $closed = false;
                break;
            }
        }

        if (!$closed) {
            throw new \Exception('Failed to close batch');
        }
    }

    /**
     * Deletes open filing by ID
     * @param $id
     * @throws \Exception
     */
    public function deleteOpenFiling($id)
    {
        $this->neededConnection();
        $openFilings = $this->getOpenFilings();
        $deleteUrl = null;
        foreach ($openFilings AS $openFiling) {
            if ($openFiling['id'] == $id) {
                $deleteUrl = $openFiling['deleteUrl'];
                break;
            }
        }

        if (!$deleteUrl) {
            throw new \Exception('Unable to find filing or filling has open batches');
        }

        $this->httpRequest->get($deleteUrl);

        $openFilings = $this->getOpenFilings();
        $deleted = true;
        foreach ($openFilings AS $openFiling) {
            if ($openFiling['id'] == $id) {
                $deleted = false;
                break;
            }
        }

        if (!$deleted) {
            throw new \Exception('Failed to delete filling');
        }
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function deleteOpenBatch($id)
    {
        $this->neededConnection();
        $openBatches = $this->getOpenBatches();
        $deleteUrl = null;
        foreach ($openBatches AS $openBatch) {
            if ($openBatch['id'] == $id) {
                $deleteUrl = $openBatch['deleteUrl'];
                break;
            }
        }

        if (!$deleteUrl) {
            throw new \Exception('Unable to find batch or batch has open packaged');
        }

        $this->httpRequest->get($deleteUrl);

        $openBatches = $this->getOpenBatches();
        $deleted = true;
        foreach ($openBatches AS $openBatch) {
            if ($openBatch['id'] == $id) {
                $deleted = false;
                break;
            }
        }

        if (!$deleted) {
            throw new \Exception('Failed to delete batch');
        }
    }

    /**
     * Closes open filing by ID
     * @param $id
     * @throws \Exception
     */
    public function closeOpenFiling($id)
    {
        $this->neededConnection();
        $openFilings = $this->getOpenFilings();
        $closeUrl = null;
        foreach ($openFilings AS $openFiling) {
            if ($openFiling['id'] == $id) {
                $closeUrl = $openFiling['closeUrl'];
                break;
            }
        }

        if (!$closeUrl) {
            throw new \Exception('Unable to find filing');
        }

        $this->httpRequest->get($closeUrl);

        $openFilings = $this->getOpenFilings();
        $closed = true;
        foreach ($openFilings AS $openFiling) {
            if ($openFiling['id'] == $id) {
                $closed = false;
                break;
            }
        }

        if (!$closed) {
            throw new \Exception('Failed to close filing');
        }
    }
}
