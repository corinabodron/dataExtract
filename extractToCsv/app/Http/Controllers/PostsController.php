<?php

namespace App\Http\Controllers;

use http\Url;
use Illuminate\Http\Request;
use KubAT\PhpSimple\HtmlDomParser;
use App\Http\Requests;

class PostsController extends Controller
{
    public function getFileContent()
    {
        for ($k = 2019; $k <2020; $k++) { //parcurgem anul 2019
            for ($j = 1; $j < 13; $j++) { //parcurgem lunile
                for ($i = 1; $i < 32; $i++) { //parcurgem zilele


                    //CURL este o bibliotecă PHP care am  importat-o pt a  descarca date prin HTTP.
                    // initiem o sesiune  curl
                    $ch = curl_init();

                    //Cu curl_setopt setam o optiune la sesiunea curl.
                    // Optiunea CURLOPT_URL este pentru a specifica url-ul ce urmeaza vizitat.
                    curl_setopt($ch, CURLOPT_URL, "https://www.opcom.ro/pp/grafice_ip/raportPIPsiVolumTranzactionat.php?lang=ro");

                    //facem o cerere de tip post, 1-true
                    curl_setopt($ch, CURLOPT_POST, 1);

                    // indicam parametri cu care
                    //facem cererea de post si le concatenam cu variabilele din for pentru a genera date diferite.
                    curl_setopt($ch, CURLOPT_POSTFIELDS,
                        "day=" . $i . "&month=" . $j . "&year=".$k."&buton=Refresh");

                    //CURLOPT_RETURNTRANSFER-returneaza si nu afiseaza direct ce se afla pe pagina
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    //Executam sesiunea cURL $ch si afisam in browser rezultatul
                    $server_output = curl_exec($ch);

                    //HtmlDomParser returneaza continutul paginii HTML
                    $dom = HtmlDomParser::str_get_html($server_output);

                    // cautarea este facuta dupa clasa border_table aceasta returneaza doua tabele
                    $tabel = $dom->find('.border_table');

                    //initializarea unui array gol
                    $rezultat = [];

                    //pentru ca suntem interesati de tabelul de pe pozitia 0 ,parcurgem continutul acestuia pe <tr>
                    foreach ($tabel[0]->children as $tableRow) {
                        $rezultatSecundar = [];

                        //parcurgem <td>
                        foreach ($tableRow->children as $td) {

                            //returneaza  textul fara atribute din element
                            $rezultatSecundar [] = $td->plaintext;
                        }
                        // salvez rezultatul secundar in array
                        $rezultat[] = $rezultatSecundar;
                    }
                    //crearea si salvarea datelor extrase intr-un fisier de tip csv
                    $fp = fopen('ziua ' . $i . ' luna ' . $j . ' anul '.$k.'.csv', 'w');
                    foreach ($rezultat as $fields) {
                        fputcsv($fp, $fields);
                    }
                    fclose($fp);
                }
            }
        }
        echo "Fisierele pentru anul 2019 au fost exportate cu succes";
    }
}


