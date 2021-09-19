<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use League\Csv\Reader;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SireneController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Encode array from latin1 to utf8 recursively
     * @param $dat
     * @return array|string
     */
    public static function getCorrectFormat($dat)
    {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } else {
            $ret = [];
            // fill out the array with the encoded values
            foreach ($dat as $i => $d) $ret[$i] = self::getCorrectFormat($d);

            return $ret;
        } 
    }
    /**
     * @Route("/sireneCsv/{id}", 
     * name="sireneCsv", 
     * methods={"GET"}, 
     * requirements={"id"="\d+"})
     */
    public function GetEntrepriseFromCsv(int $id): Response
    {
        $csv = Reader::createFromPath('../public/sirene-infos.csv', 'r');
        // Selects the record to be used as the CSV header.
        $csv->setHeaderOffset(0);
        // Sets the field delimiter.
        $csv->setDelimiter(';');

        foreach($csv->getRecords() as $record){
            if($record['SIREN'] == $id){
                return $this->json($this->getCorrectFormat($record), 200, [
                    "Content-type" => "application/json"
                ]);

                // if(!empty($record['ENSEIGNE'])){
                //     return $this->json($record['ENSEIGNE'], 200, [
                //         "Content-type" => "application/json"
                //     ]);
                // } else {
                //     return $this->json("This entreprise exist but it has no name.", 200, [
                //         "Content-type" => "application/json"
                //     ]);
                // } 
            };
        }
        // if we return here, this means there's no entreprise with this sirene Id
        return $this->json("This sirene number $id do not exist.", 404, [
            "Content-type" => "application/json"
        ]);
    }

    /**
     * @Route("/sirene/{id}", 
     * name="sirene", 
     * methods={"GET"}, 
     * requirements={"id"="\d+"})
     */
    public function GetEntrepriseFromSireneApi(int $id): Response
    {
        try{
            $response = $this->client->request(
                'GET',
                "https://api.insee.fr/entreprises/sirene/V3/$id"
            );

            if($response->getStatusCode() != 200){
                return $this->json("This sirene number $id do not exist.", $response->getStatusCode(), [
                    "Content-type" => "application/json"
                ]);
            }
        } catch(TransportExceptionInterface $e){
            return $this->json("Something went wrong while interrogating the sirene Api :" . $e->getMessage(), 400, [
                "Content-type" => "application/json"
            ]);
        }
        // I'm returning Response directly because getContent() return a string in Json format 
        return new Response($response->getContent());
    }
}
