<?php

// Add autoload
require __DIR__ . '/../vendor/autoload.php';

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;

// Authenticate
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/paywalld-app-17f7a8868bd3.json');

$client = new Google_Client;
$client->useApplicationDefaultCredentials();

$client->setApplicationName("Something to do with my representatives");
$client->setScopes(['https://www.googleapis.com/auth/drive', 'https://spreadsheets.google.com/feeds']);

if ($client->isAccessTokenExpired()) {
  $client->refreshTokenWithAssertion();
}

$accessToken = $client->fetchAccessTokenWithAssertion()["access_token"];
ServiceRequestFactory::setInstance(
  new DefaultServiceRequest($accessToken)
);


/**
 * Custom masternode handling class
 * Class masterNode
 */
class masterNode {

  /**
   * Return specific masternode data
   * @param null $mn_id
   */
  public static function getMasternodeData($coin, $mn_id = NULL) {

    try {
      // Get our spreadsheet
      $spreadsheet = (new Google\Spreadsheet\SpreadsheetService)
        ->getSpreadsheetFeed()
        ->getByTitle('Omegapool '. strtoupper($coin) . ' Shared Master Node #' . $mn_id);

      // Get the first worksheet (tab)
      $worksheets = $spreadsheet->getWorksheetFeed()->getEntries();
      $worksheet = $worksheets[0];

      $listFeed = $worksheet->getListFeed();
    } catch (\Exception $e) {
      return [];
    }

    $mn_data = [];
    if (!empty($listFeed)) {
      /** @var ListEntry */
      foreach ($listFeed->getEntries() as $entry) {
        $mn_data[] = $entry->getValues();
      }
    }

    return $mn_data;

  }
}
