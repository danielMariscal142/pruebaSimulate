<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use League\OAuth1\Client\Client;
use Illuminate\Support\Facades\Http;
   use League\OAuth1\Client\Server\Server;
   use League\OAuth1\Client\Credentials\ClientCredentials;
   use League\OAuth1\Client\Credentials\TokenCredentials;
   use League\OAuth1\Client\Signature\HmacSha1Signature;
   use League\OAuth1\Client\Signature\HmacSha256Signature;
class NetSuiteController extends Controller
{
   public function makeRequest()
   {
    $dataUser = [
        'name' => "John Doe",
        'address' => "123 Main St",
        'phone' => "555-123-4567",
        'region' => "Some Region",
        'zip' => "12345",
        'comuna' => "Some Comuna"
    ];
       // Definir las credenciales del cliente y el token
       $clientCredentials = new ClientCredentials();
       $clientCredentials->setIdentifier("ff82aed2d0b2f8f94d7ec4b4c5565eee33c9b973d4b0696f24fa01dcab1d69f7");
       $clientCredentials->setSecret("6016c7f2425d038491d6bbd5a98a380ea185668efc8e75ce31303c460329fef3");

       $tokenCredentials = new TokenCredentials();
       $tokenCredentials->setIdentifier("51d403c0524d8b4cabfb1943db460fc4e9f88e189592db2b1077ce0fcb61d53f");
       $tokenCredentials->setSecret("16078c7872d6f488babce1e2414ef497534dcf209fbebff293bd84610a4f3eb4");

       // Crear la instancia del servidor OAuth
       $server = new Server($clientCredentials, new HmacSha256Signature());

       // Crear la solicitud HTTP
       $httpRequest = $server->createHttpClient();

       // Obtener la firma OAuth
       $oauthHeader = $server->getHeaders($tokenCredentials, 'POST', 'https://4888975-sb1.restlets.api.netsuite.com/app/site/hosting/restlet.nl?script=806&deploy=1', []);

       // Definir los datos para enviar
       $requestData = [
           'type' => "create_customer",
           'customform' => 112,
           'entityid' => $dataUser['name'],
           'isperson' => "F",
           'subsidiary' => 4,
           'entitystatus' => 13,
           'territory' => 2,
           'custentity18' => 1,
           'custentity19' => 8,
           'companyname' => $dataUser['name'],
           'address' => [
               [
                   'country' => "CH",
                   'addressee' => $dataUser['address'],
                   'addrphone' => $dataUser['phone'],
                   'city' => $dataUser['region'],
                   'zip' => $dataUser['zip'],
                   'addr1' => $dataUser['comuna'],
               ],
           ],
           'contacts' => [],
       ];

       // Realizar la solicitud HTTP
       try {
           $response = $httpRequest->post('https://4888975-sb1.restlets.api.netsuite.com/app/site/hosting/restlet.nl?script=806&deploy=1', [
               'headers' => array_merge(['Content-Type' => 'application/json'], $oauthHeader),
               'json' => $requestData,
           ]);

           return json_decode($response->getBody(), true);
       } catch (Exception $e) {
           throw new Exception("Error al Insertar");
       }
       try {
           $response = createUser($dataUser);
           print_r($response);
       } catch (Exception $e) {
           echo $e->getMessage();
       }
   }

}
