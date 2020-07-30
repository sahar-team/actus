<?php
/*
Plugin name: RSS Actu Plugin - Modified
plugin URI: none
description: Fetch data from les Echos
version: 0.0.1
Author: Antoine Franz
license: GPL2
*/

// Search post by id, returns true if it exists
function post_exists_by_id($id)
{
    return is_string(get_post_status($id));
}

// Check if slug exists in DB
function the_slug_exists($post_name) {
    global $wpdb;
    if($wpdb->get_row("SELECT post_name FROM wp_posts WHERE post_name = '" . $post_name . "'", 'ARRAY_A')) {
        return true;
    } else {
        return false;
    }
}

// Counts all the posts from the categories provided
function get_category_posts_count ($categories_array)
{
  $posts_count = 0;
  foreach ($categories_array as $category) {
    $cat_count = get_category(get_cat_ID($category));
    $posts_count += $cat_count->count;
  }
  // $cat_count = get_category(get_cat_ID("dessins"));
  // $posts_count += $cat_count->count;
return($posts_count);
}


function merge_article_arrays ($array1, $array2)
{
  $array1["count"] += $array2["count"];
  $array1["article_names"] = array_merge($array1["article_names"], $array2["article_names"]);
  $array1["article_ids"] = array_merge($array1["article_ids"], $array2["article_ids"]);
  $array1["article_categories"] = array_merge($array1["article_categories"], $array2["article_categories"]);
  $array1["publication_date"] = array_merge($array1["publication_date"], $array2["publication_date"]);
  // $array1["article_names"] += $array2["article_names"];
  // $array1["article_ids"] += $array2["article_ids"];
  // $array1["article_categories"] += $array2["article_categories"];

  return($array1);
}

add_shortcode('rsscode', 'rss_shortcodeactu');
function rss_shortcodeactu()
{
    global $wpdb;
    $display = "";
    return ($display);
}

function actu_admin_menu_option()
{
    add_menu_page('Scripts', 'Sahar actus plugin', 'manage_options', 'actu-admin-menu', 'actu_scripts_page', '', 200);
}


add_action('admin_menu', 'actu_admin_menu_option');


function count_articles_of_category($result, $rubrique, $file, $type, $xmlString, $isNews, $summaryString)
{
  $articles_array = [
    "count" => 0,
    "article_names" => [],
    "article_ids" => [],
    "article_categories" => [],
    "publication_date" => [],
];

    $index = 0;
    $article_count = 0;
    foreach ($result as $key => $article) {

        $create_date = $article->create_date;
        $id = $article->id;

        $today = new DateTime();
        $expiry_date = $create_date;
        $expiry_date = new DateTime($expiry_date);
        $interval = $today->diff($expiry_date);
        $day = -($interval->format('%r%a'));

        if ($rubrique == 'ec_echeancier') {
            $numDay = 200;
        } else {
            $numDay = 90; // DAYS
        }

        if ($day < $numDay) {

            $article_count++;
            $display_date = $article->display_date;
            $create_date = $article->create_date;
            $author = $article->author;
            $language = $article->language;
            $author = $article->author;
            $title = $article->title;
            // $articles_array["article_names"] . $title;
            array_push($articles_array["article_names"], $title);
            array_push($articles_array["article_ids"], $article->id);
            $summary2 = explode("</summary>", $summaryString[$index])[0];
            $summary3 = str_replace("&eacute;", "é", $summary2);
            $summary4 = str_replace("&egrave;", "è", $summary3);
            $summary5 = str_replace("&agrave;", "à", $summary4);
            $summary = str_replace("&ugrave;;", "ù", $summary5);
            $media = $article->media;
            $contentFinal = explode("</section_content>", $xmlString[$index])[0];

            if ($isNews == "ouiSection") {
                $contentFinal = explode("</section>", $xmlString[$index])[0];
            }

            $index++;





            $contentFinal = $contentFinal . "<br /><br /><span style='font-size: 10px !important; color: #ccc !important;'>Copyright Les Echos Publishing - 2020</span>";

            $originalDate = explode(" ", $create_date)[0];
            $newDate = date("d/m/Y", strtotime($originalDate));
            $category = "";


            switch ($rubrique) {
                case "ec_dessins":
                    $category = "dessins";
                    break;
                case "ec_fiscal":
                    $category = "fiscal";
                    break;
                case "ec_gestion":
                    $category = "gestion";
                    break;
                case "ec_juridique":
                    $category = "juridique";
                    break;
                case "ec_metiers_associatif":
                    $category = "associatif";
                    break;
                case "ec_metiers_professions":
                    $category = "professions";
                    break;
                case "ec_minute-de-l-expert":
                    $category = "minute";
                    break;
                case "ec_multimedia":
                    $category = "multimedia";
                    break;
                case "ec_patrimoine":
                    $category = "patrimoine";
                    break;
                case "ec_social":
                    $category = "social";
                    break;
            }
            $status = "publish";

            if ((($rubrique == "ec_fiscal") && ($type == "Chiffres")) || (($rubrique == "ec_fiscal") && ($type == "Questions")) || (($rubrique == "ec_gestion") && ($type == "Questions")) || (($rubrique == "ec_juridique") && ($type == "Questions")) || (($rubrique == "ec_juridique") && ($type == "Chiffres")) || (($rubrique == "ec_fiscal") && ($type == "Questions")) || (($rubrique == "ec_multimedia") && ($type == "Questions")) || (($rubrique == "ec_patrimoine") && ($type == "Questions")) || (($rubrique == "ec_social") && ($type == "Questions")) || (($rubrique == "ec_social") && ($type == "Chiffres")) || (($rubrique == "ec_social") && ($type == "Chiffre")) || (($rubrique == "ec_social") && ($type == "Aides à l\'embauche")) || ($rubrique == "ec_echeancier")) {

                $category = "newsletter";
            }

            if ($isNews == "oui") {
                $category = "newsletter";
            }



            $url = 'http://www.ar24-studio.com/wp-content/uploads/' . date("Y") . '/' . date('m') . '/' . $media;
            if ($type == "Questions") {
                $url = "https://www.amplitudeinterim.fr/actus/xactu-39.jpg.pagespeed.ic.puvvpUtkP8.jpg";
            }

            if ($rubrique == "ec_echeancier") {
                $contentArticle = '<h3>'. $title . '</h3><br>' . '<b><h4>Publié le ' . $newDate . '</h4><b><br /><em>' . $type . '</em>'  . $summary .  $contentFinal;
            } else {
                $contentArticle = '<h3>'. $title . '</h3><br>' . '<b><h4>Publié le ' . $newDate . '</h4><b><br /><em>' . $type . '</em>' . $summary . '<img src="' . $url . '" style="width: 100%;" />' . $contentFinal;
            }



            $keywords = array();

            foreach ($article->tag as $tag) {
                if ($tag->attributes() == "keyword") {
                    array_push($keywords, $tag);
                }
            }
            $typo = $type;
            $copyright = $article->copyright;
            $summaryNormalized = substr($summary, 0, 145) . "...";

            $my_post = array(
                'post_author'   => 1,
                'post_content'  => $contentArticle,
                'post_title'    => (string)$title,
                'post_status'   => $status,
                'post_excerpt' => (string)$summaryNormalized,
                'post_name' => (string)$id,
                'guid' => $id,

                'tags_input' => $keywords,
                'meta_input' => array(
                    'authorArticle' => (string)$author,
                    'dateArticle' => (string)$create_date,
                    'imageArticle' => (string)$media
                )



            );
            require_once(ABSPATH . 'wp-admin/includes/post.php');
              array_push($articles_array["article_categories"], $category);
              array_push($articles_array["publication_date"], $my_post["meta_input"]["dateArticle"]);
        }
    }
    $articles_array["count"] = $article_count;
    return ($articles_array);
}


function page_treatment_loi_fin($page)
{

    $articles2 = explode("<section>", $page);
    $articles3 = array_slice($articles2, 1);


    $articles = str_replace(


        array("<section_title>", "</section_title>", "<section_summary>", "</section_summary>", "<section_author>", "</section_author>", "<section_content>", "</section_content>", "<texteparagraphe>", "</texteparagraphe>", "<ref_lien ", "</ref_lien>", "<annotation>", "</annotation>", "<titreannotation>", "</titreannotation>", "<video>&lt;![CDATA[&lt;", "data-width", "data-height", "/video&gt;]]", "<titrechapitre>", "</titrechapitre>", "<titreparagraphe>", "</titreparagraphe>", "<intertitre>", "</intertitre>", "<titreencadre>", "</titreencadre>", "<texteencadre>", "</texteencadre>", "<source>", "</source>", "<reference>", "</reference>", "<lien ", "</lien>", "<souligne>", "</souligne>", "<gras>", "</gras>", "<exposant>", "</exposant>", "<italique>", "</italique>", "<retourligne>"),
        array("<h3>", "</h3>", "<p><b>", "</b></p>", "<p hidden>", "</p>", "<p>", "</p>", "<p>", "</p>", "<a ", "</a>", "<p><em>", "</em></p>", "<b>", "</b>", "<", "width", "height", "</video>", "<h3>", "</h3>", "<h4>", "</h4>", "<h4>", "</h4>", "<h4>", "</h4>", "<p>", "</p>", "<p><em>", "</em>,</p>", "<p><em>", "</em></p>", "<a ", "</a>", "<u>", "</u>", "<b>", "</b>", "<sup>", "</sup>", "<i>", "</i>", "<br />"),
        $articles3
    );
    return $articles;
}

function page_treatment_summary($page)
{

    $articles2 = explode("<summary>", $page);
    $articles3 = array_slice($articles2, 1);


    $articles = str_replace(


        array("<section_title>", "</section_title>", "<section_summary>", "</section_summary>", "<section_author>", "</section_author>", "<section_content>", "</section_content>", "<texteparagraphe>", "</texteparagraphe>", "<ref_lien ", "</ref_lien>", "<annotation>", "</annotation>", "<titreannotation>", "</titreannotation>", "<video>&lt;![CDATA[&lt;", "data-width", "data-height", "/video&gt;]]", "<titrechapitre>", "</titrechapitre>", "<titreparagraphe>", "</titreparagraphe>", "<intertitre>", "</intertitre>", "<titreencadre>", "</titreencadre>", "<texteencadre>", "</texteencadre>", "<source>", "</source>", "<reference>", "</reference>", "<lien ", "</lien>", "<souligne>", "</souligne>", "<gras>", "</gras>", "<exposant>", "</exposant>", "<italique>", "</italique>", "<retourligne>"),
        array("<h3>", "</h3>", "<p><b>", "</b></p>", "<p hidden>", "</p>", "<p>", "</p>", "<p>", "</p>", "<a ", "</a>", "<p><em>", "</em></p>", "<b>", "</b>", "<", "width", "height", "</video>", "<h3>", "</h3>", "<h4>", "</h4>", "<h4>", "</h4>", "<h4>", "</h4>", "<p>", "</p>", "<p><em>", "</em>,</p>", "<p><em>", "</em></p>", "<a ", "</a>", "<u>", "</u>", "<b>", "</b>", "<sup>", "</sup>", "<i>", "</i>", "<br />"),
        $articles3
    );
    return $articles;
}

if (!function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

function page_treatment($page)
{

    $articles2 = explode("<section_content>", $page);
    $articles3 = array_slice($articles2, 1);


    $articles = str_replace(
        array("<texteparagraphe>", "</texteparagraphe>", "<ref_lien ", "</ref_lien>", "<annotation>", "</annotation>", "<titreannotation>", "</titreannotation>", "<video>&lt;![CDATA[&lt;", "data-width", "data-height", "/video&gt;]]", "<titrechapitre>", "</titrechapitre>", "<titreparagraphe>", "</titreparagraphe>", "<intertitre>", "</intertitre>", "<titreencadre>", "</titreencadre>", "<texteencadre>", "</texteencadre>", "<source>", "</source>", "<reference>", "</reference>", "<lien ", "</lien>", "<souligne>", "</souligne>", "<gras>", "</gras>", "<exposant>", "</exposant>", "<italique>", "</italique>", "<retourligne>"),
        array("<p>", "</p>", "<a ", "</a>", "<p><em>", "</em></p>", "<b>", "</b>", "<", "width", "height", "</video>", "<h3>", "</h3>", "<h4>", "</h4>", "<h4>", "</h4>", "<h4>", "</h4>", "<p>", "</p>", "<p><em>", "</em>,</p>", "<p><em>", "</em></p>", "<a ", "</a>", "<u>", "</u>", "<b>", "</b>", "<sup>", "</sup>", "<i>", "</i>", "<br />"),
        $articles3
    );
    return $articles;
}



function start_test()
{
    // do things. If successful, return true. Otherwise return false
    return true;
}

// add_action('admin_post_start_test', 'start_test');


function get_article_count()
{

  $articles_array = [
    "count" => 0,
    "article_names" => [],
    "article_ids" => [],
    "article_categories" => [],
    "publication_date" => [],
];
    $ftp = "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/";
    $article_count = 0;
    $rubriques = array('ec_dessins', 'ec_fiscal', 'ec_gestion', 'ec_juridique', 'ec_metiers', 'ec_minute-de-l-expert', 'ec_multimedia', 'ec_patrimoine', 'ec_social', 'ec_echeancier');

    foreach ($rubriques as $name) {
        $file = $ftp . $name;



        if ($name == "ec_dessins") {

            $basePath = $file . "/";
            // Dessins
            $fullPath = $basePath . 'ec_flux_dessins.xml';
            $resultActu = simplexml_load_file($fullPath);


            $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
            $summary = page_treatment_summary($page);
            $xmlString = page_treatment($page);


            // $article_count += count_articles_of_category($resultActu, $name, $basePath, 'Dessin', $xmlString, "non", $summary);
            $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $name, $basePath, 'Dessin', $xmlString, "non", $summary));
          }
        elseif ($name == "ec_metiers") {
            $basePath = $file . "/Associatifs/tout_flux/";
            $fullPath = $basePath . 'ec_flux_metiers_associatifs.xml';
            $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
            $xmlString = page_treatment($page);
            $summary = page_treatment_summary($page);
            $resultActu = simplexml_load_file($fullPath);
            $nameBis = "ec_metiers_associatif";
            // $article_count += count_articles_of_category($resultActu, $nameBis, $basePath, 'Actualités', $xmlString, "non", $summary);
            $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $nameBis, $basePath, 'Actualités', $xmlString, "non", $summary));

            $basePath = $file . "/Professions_Liberales/tout_flux/";
            $fullPath = $basePath . 'ec_flux_metiers_professions_liberales.xml';
            $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
            $xmlString = page_treatment($page);
            $summary = page_treatment_summary($page);
            $resultActu = simplexml_load_file($fullPath);
            $nameBis = "ec_metiers_professions";
            // $article_count += count_articles_of_category($resultActu, $nameBis, $basePath, 'Actualités', $xmlString, "non", $summary);
            $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $nameBis, $basePath, 'Actualités', $xmlString, "non", $summary));

            $basePath = $file . "/Agricoles/tout_flux/";
            $fullPath = $basePath . 'ec_flux_metiers_agricoles.xml';
            $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
            $xmlString = page_treatment($page);
            $summary = page_treatment_summary($page);
            $resultActu = simplexml_load_file($fullPath);
            $nameBis = "ec_metiers_professions";
            // $article_count += count_articles_of_category($resultActu, $nameBis, $basePath, 'Actualités', $xmlString, "oui", $summary);
            $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $nameBis, $basePath, 'Actualités', $xmlString, "oui", $summary));

        }
        elseif ($name == "ec_minute-de-l-expert") {

            $basePath = $file . "/tout_flux/";
            $fullPath = $basePath . 'ec_flux_minute_de_l_expert.xml';
            $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
            $xmlString = page_treatment($page);
            $summary = page_treatment_summary($page);
            $resultActu = simplexml_load_file($fullPath);
            // $article_count += count_articles_of_category($resultActu, $name, $basePath, 'Interview', $xmlString, "non", $summary);
            $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $name, $basePath, 'Interview', $xmlString, "non", $summary));
        } elseif ($name == "ec_echeancier") {

            $basePath = $file . "/Mois/tout_flux/";
            $fullPath = $basePath . 'ec_flux_echeancier.xml';
            $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
            $xmlString = page_treatment($page);
            $summary = page_treatment_summary($page);
            $resultActu = simplexml_load_file($fullPath);
            // $article_count += count_articles_of_category($resultActu, $name, $basePath, 'Echéancier', $xmlString, "non", $summary);
            $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $name, $basePath, 'Echéancier', $xmlString, "non", $summary));
        } else {




            if ($name == "ec_social") {

                $basePath = $file . "/Aides-a-l-embauche/tout_flux/";
                $fullPath = $basePath . 'ec_flux_aides.xml';
                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                $xmlString = page_treatment($page);
                $summary = page_treatment_summary($page);
                $resultQuestions = simplexml_load_file($fullPath);
                // $article_count += count_articles_of_category($resultQuestions, $name, $basePath, 'Aides à l\'embauche', $xmlString, "non", $summary);
                $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultQuestions, $name, $basePath, 'Aides à l\'embauche', $xmlString, "non", $summary));

                $basePath = $file . "/paie/tout_flux/";
                $fullPath = $basePath . 'ec_flux_paie.xml';
                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                $xmlString = page_treatment($page);
                $summary = page_treatment_summary($page);
                $resultQuestions = simplexml_load_file($fullPath);
                // $article_count += count_articles_of_category($resultQuestions, $name, $basePath, 'Chiffre', $xmlString, "non", $summary);
                $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultQuestions, $name, $basePath, 'Chiffre', $xmlString, "non", $summary));

                $basePath = $file . "/Actualites/tout_flux/";
                $fullPath = $basePath . 'ec_flux_actualites.xml';
                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                $xmlString = page_treatment($page);
                $summary = page_treatment_summary($page);
                $resultActu = simplexml_load_file($fullPath);
                // $article_count += count_articles_of_category($resultActu, $name, $basePath, 'Actualités', $xmlString, "non", $summary);
                $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $name, $basePath, 'Actualités', $xmlString, "non", $summary));


                $basePath = $file . "/Chiffres-utiles/tout_flux/";
                $fullPath =  $basePath . 'ec_flux_chiffres.xml';
                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                $xmlString = page_treatment($page);
                $summary = page_treatment_summary($page);
                $resultChiffresUtiles = simplexml_load_file($fullPath);
                // $article_count += count_articles_of_category($resultChiffresUtiles, $name, $basePath, 'Chiffres', $xmlString, "non", $summary);
                $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultChiffresUtiles, $name, $basePath, 'Chiffres', $xmlString, "non", $summary));




                $basePath = $file . "/Dossiers/tout_flux/";
                $fullPath = $basePath . 'ec_flux_dossiers.xml';
                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                $xmlString = page_treatment($page);
                $summary = page_treatment_summary($page);
                $resultDossiers = simplexml_load_file($fullPath);
                // $article_count += count_articles_of_category($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary);
                $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary));

                $basePath = $file . "/Questions-reponses/tout_flux/";
                $fullPath = $basePath . 'ec_flux_faq.xml';
                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                $xmlString = page_treatment($page);
                $summary = page_treatment_summary($page);
                $resultQuestions = simplexml_load_file($fullPath);
                // $article_count += count_articles_of_category($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary);
                $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary));
            } else {
                if ($name == "ec_gestion") {
                    $basePath = $file . "/Dossiers/tout_flux/";
                    $fullPath = $basePath . 'ec_flux_dossiers.xml';
                    $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                    $xmlString = page_treatment($page);
                    $summary = page_treatment_summary($page);
                    $resultDossiers = simplexml_load_file($fullPath);
                    // $article_count += count_articles_of_category($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary);
                    $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary));

                    $basePath = $file . "/Questions-reponses/tout_flux/";
                    $fullPath = $basePath . 'ec_flux_faq.xml';
                    $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                    $xmlString = page_treatment($page);
                    $summary = page_treatment_summary($page);
                    $resultQuestions = simplexml_load_file($fullPath);
                    // $article_count += count_articles_of_category($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary);
                    $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary));
                } else {

                    if (($name == "ec_patrimoine") || ($name == "ec_multimedia")) {

                        $basePath = $file . "/Actualites/tout_flux/";
                        $fullPath = $basePath . 'ec_flux_actualites.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultActu = simplexml_load_file($fullPath);
                        // $article_count += count_articles_of_category($resultActu, $name, $basePath, 'Actualités', $xmlString, "non", $summary);
                        $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $name, $basePath, 'Actualités', $xmlString, "non", $summary));




                        $basePath = $file . "/Dossiers/tout_flux/";
                        $fullPath = $basePath . 'ec_flux_dossiers.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultDossiers = simplexml_load_file($fullPath);
                        // $article_count += count_articles_of_category($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary);
                        $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary));

                        $basePath = $file . "/Questions-reponses/tout_flux/";
                        $fullPath = $basePath . 'ec_flux_faq.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $resultQuestions = simplexml_load_file($fullPath);
                        // $article_count += count_articles_of_category($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary);
                        $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary));
                    } else {




                        // Actualités
                        $basePath = $file . "/Actualites/tout_flux/";
                        $fullPath = $basePath . 'ec_flux_actualites.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultActu = simplexml_load_file($fullPath);
                        // $article_count += count_articles_of_category($resultActu, $name, $basePath, 'Actualités', $xmlString, "non", $summary);
                        $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $name, $basePath, 'Actualités', $xmlString, "non", $summary));


                        $basePath = $file . "/Chiffres-utiles/tout_flux/";
                        $fullPath =  $basePath . 'ec_flux_chiffres.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultChiffresUtiles = simplexml_load_file($fullPath);
                        // $article_count += count_articles_of_category($resultChiffresUtiles, $name, $basePath, 'Chiffres', $xmlString, "non", $summary);
                        $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultChiffresUtiles, $name, $basePath, 'Chiffres', $xmlString, "non", $summary));




                        $basePath = $file . "/Dossiers/tout_flux/";
                        $fullPath = $basePath . 'ec_flux_dossiers.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultDossiers = simplexml_load_file($fullPath);
                        // $article_count += count_articles_of_category($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary);
                        $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary));

                        $basePath = $file . "/Questions-reponses/tout_flux/";
                        $fullPath = $basePath . 'ec_flux_faq.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultQuestions = simplexml_load_file($fullPath);
                        // $article_count += count_articles_of_category($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary);
                        $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary));
                    }
                }
            }
        }

        if ($name == "ec_social") {

            $fullPath = "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_loi-de-finances/Fiscalite-personnelle/tout_flux/ec_flux_loi_de_finances_personnelle.xml";
            $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
            $xmlString = page_treatment_loi_fin($page);
            $summary = page_treatment_summary($page);

            $resultActu = simplexml_load_file($fullPath);
            // $article_count += count_articles_of_category($resultActu, $name, "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_loi-de-finances/Fiscalite-personnelle", 'Actualités', $xmlString, "ouiSection", $summary);
            $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $name, "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_loi-de-finances/Fiscalite-personnelle", 'Actualités', $xmlString, "ouiSection", $summary));

            $fullPath = "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_loi-de-finances/Fiscalite-professionnelle/tout_flux/ec_flux_loi_de_finances_professionnelle.xml";
            $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
            $summary = page_treatment_summary($page);
            $xmlString = page_treatment_loi_fin($page);
            $resultActu = simplexml_load_file($fullPath);
            // $article_count += count_articles_of_category($resultActu, $name, "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_loi-de-finances/Fiscalite-professionnelle", 'Actualités', $xmlString, "ouiSection", $summary);
            $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $name, "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_loi-de-finances/Fiscalite-professionnelle", 'Actualités', $xmlString, "ouiSection", $summary));




            $fullPath = "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_tout_flux/ec_flux_faq.xml";
            $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
            $xmlString = page_treatment($page);
            $summary = page_treatment_summary($page);
            $resultActu = simplexml_load_file($fullPath);
            // $article_count += count_articles_of_category($resultActu, $name, 'ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_tout_flux', 'Actualités', $xmlString, "oui", $summary);
            $articles_array = merge_article_arrays($articles_array, count_articles_of_category($resultActu, $name, 'ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_tout_flux', 'Actualités', $xmlString, "oui", $summary));
        }
        }
    return ($articles_array);
}


class RSS_Actu_Plugin
{


    const CRON_HOOK = 'initData';

    public function setupCronJob()
    {
        //Use wp_next_scheduled to check if the event is already scheduled
        $timestamp = wp_next_scheduled(self::CRON_HOOK);

        //If $timestamp == false schedule daily backups since it hasn't been done previously
        if ($timestamp == false) {
            //Schedule the event for right now, then to repeat daily using the hook 'update_whatToMine_api'
            wp_schedule_event(time(), 'twicedaily', self::CRON_HOOK);
        }
    }

    public function unsetCronJob()
    {
        // Get the timestamp for the next event.
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        wp_unschedule_event($timestamp, self::CRON_HOOK);
    }



    public function __construct()
    {
        //add triger on install
        register_activation_hook(__FILE__, array($this, 'on_install'));
        register_deactivation_hook(__FILE__, array($this, 'on_deactivation'));
        add_action(self::CRON_HOOK, array($this, 'init_data'));
    }


    public static function on_deactivation()
    {
        global $wpdb;
        $this->unsetCronJob();
    }

    public static function on_install()
    {
        global $wpdb; //variable wordpress

        $charset_collate = $wpdb->get_charset_collate(); //encodage UTF8

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


        //creation structure de la base de donnée
        $table_master = $wpdb->prefix . 'rss_actu_master';
        $this->setupCronJob();
        $this->init_data();
    }


    public function cron_task()
    { }


    //récupération des informations présentent dans le flux
    public function get_data_master()
    {
        $data = array();
    }

    //initialisation des informations dans la base de données à l'ajout du plugin
    public function init_data()
    {

        global $wpdb; //variable wordpress
        $rubriques = array('ec_dessins', 'ec_fiscal', 'ec_gestion', 'ec_juridique', 'ec_metiers', 'ec_minute-de-l-expert', 'ec_multimedia', 'ec_patrimoine', 'ec_social', 'ec_echeancier');

        function Generate_Featured_Image($image_url, $post_id)
        {
            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents($image_url);
            $filename = basename($image_url);
            if (wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
            else                                    $file = $upload_dir['basedir'] . '/' . $filename;
            file_put_contents($file, $image_data);


            $wp_filetype = wp_check_filetype($filename, null);

            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment($attachment, $file, $post_id);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            $res1 = wp_update_attachment_metadata($attach_id, $attach_data);
            $res2 = set_post_thumbnail($post_id, $attach_id);
        }



        $article_count = get_article_count();
        $saved_articles = get_category_posts_count(["dessins", "social", "newsletter", "associatif", "fiscal", "gestion", "juridique", "minute", "multimedia", "patrimoine", "professions"]);
        $body = $saved_articles . " articles sauvegardés sur " . $article_count["count"] . " disponibles.";
        wp_mail( "slechani@sahar.fr", "Rapport CRON plugin Actus", $body);

        function treatment($result, $rubrique, $file, $type, $xmlString, $isNews, $summaryString)
 {
   $index = 0;
   foreach($result as $key=>$article){




    $create_date=$article->create_date;
    $id=$article->id;

    $today = new DateTime();
    $expiry_date = $create_date;
    $expiry_date = new DateTime($expiry_date);
    $interval = $today->diff($expiry_date);
    $day = -($interval->format('%r%a'));

    if ($rubrique == 'ec_echeancier') {
     $numDay = 200;
   } else {
     $numDay = 90;
   }

   if($day < $numDay) {


     $display_date=$article->display_date;
     $create_date=$article->create_date;
     $author=$article->author;
     $language=$article->language;
     $author=$article->author;
     $title=$article->title;
     $summary2= explode("</summary>", $summaryString[$index])[0];
     $summary3 = str_replace("&eacute;", "é", $summary2);
     $summary4 = str_replace("&egrave;", "è", $summary3);
     $summary5 = str_replace("&agrave;", "à", $summary4);
     $summary = str_replace("&ugrave;;", "ù", $summary5);
     $media=$article->media;
     $contentFinal= explode("</section_content>", $xmlString[$index])[0];

     if ($isNews == "ouiSection") {
     $contentFinal= explode("</section>", $xmlString[$index])[0];
   }

     $index++;





     $contentFinal = $contentFinal."<br /><br /><span style='font-size: 10px !important; color: #ccc !important;'>Copyright Les Echos Publishing - 2020</span>";

     $originalDate = explode(" ", $create_date)[0];
     $newDate = date("d/m/Y", strtotime($originalDate));
     $category="";


     switch ($rubrique) {
       case "ec_dessins":
       $category="dessins";
       break;
       case "ec_fiscal":
       $category="fiscal";
       break;
       case "ec_gestion":
       $category="gestion";
       break;
       case "ec_juridique":
       $category="juridique";
       break;
       case "ec_metiers_associatif":
       $category="associatif";
       break;
       case "ec_metiers_professions":
       $category="professions";
       break;
       case "ec_minute-de-l-expert":
       $category="minute";
       break;
       case "ec_multimedia":
       $category="multimedia";
       break;
       case "ec_patrimoine":
       $category="patrimoine";
       break;
       case "ec_social":
       $category="social";
       break;
     }
     $status="publish";

     if ((($rubrique == "ec_fiscal") && ($type == "Chiffres")) || (($rubrique == "ec_fiscal") && ($type == "Questions")) || (($rubrique == "ec_gestion") && ($type == "Questions")) || (($rubrique == "ec_juridique") && ($type == "Questions")) || (($rubrique == "ec_juridique") && ($type == "Chiffres")) || (($rubrique == "ec_fiscal") && ($type == "Questions")) || (($rubrique == "ec_multimedia") && ($type == "Questions")) || (($rubrique == "ec_patrimoine") && ($type == "Questions")) || (($rubrique == "ec_social") && ($type == "Questions")) || (($rubrique == "ec_social") && ($type == "Chiffres")) || (($rubrique == "ec_social") && ($type == "Chiffre")) || (($rubrique == "ec_social") && ($type == "Aides à l\'embauche")) || ($rubrique == "ec_echeancier")) {

       $category="newsletter";
     }

     if ($isNews == "oui") {
       $category="newsletter";
     }



     $url='http://www.ar24-studio.com/wp-content/uploads/'. date("Y") .'/'. date('m').'/'. $media;
     if ($type=="Questions") {
       $url="https://www.amplitudeinterim.fr/actus/xactu-39.jpg.pagespeed.ic.puvvpUtkP8.jpg";
     }

     if ($rubrique == "ec_echeancier") {
       $contentArticle= '<h3>' . $title . '</h3><br>' . '<b> <h4>Publié le ' . $newDate . '</h4><b><br /><em>' .$type.'</em>' .$summary. $contentFinal;



     } else {
       $contentArticle= '<h3>' . $title . '</h3><br>' . '<b><h4>Publié le ' . $newDate . '</h4><b><br /><em>' .$type.'</em> ' .$summary.'<img src="'. $url .'" style="width: 100%;" />'. $contentFinal;



     }



     $keywords=array();

     foreach($article->tag as $tag) {
       if ($tag->attributes() == "keyword") {
         array_push($keywords, $tag);
       }

     }
     $typo=$type;
     $copyright=$article->copyright;
     $summaryNormalized=substr($summary, 0, 145)."...";

     $my_post = array(
       'post_author'   => 1,
       'post_content'  =>(string) $contentArticle,
       'post_title'    => (string) $title,
       'post_status'   => (string) $status,
       'post_excerpt' => iconv('ISO-8859-1','UTF-8', (string) $summaryNormalized),
       'post_name' => (string) $id,
       'guid' => (string) $id,

       'tags_input' => $keywords,
       'meta_input' => array(
         'authorArticle' => (string) $author,
         'dateArticle' => (string) $create_date,
         'imageArticle' => (string) $media
       )



     );
     require_once( ABSPATH . 'wp-admin/includes/post.php' );
  global $wpdb;
     // if (post_exists((string) $title) == 0)
     // echo "POST SLUG : ", $my_post["post_name"], "<br>";
      // if (the_slug_exists($my_post["post_name"]) == false)
      $post_if = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_name like '". $my_post["post_name"] ."'");
      if ($post_if < 1)
     {

       // echo "unregistered ID : ", $my_post["post_name"];
       // foreach ($my_post as $key => $value) {
       //   echo "<br>key  ", $key, " => ", $value;
       // }
       if (empty($my_post["post_name"]))
        echo "Empty post_name";


       // echo "Post that shouldn't exist yet : " , $my_post["post_name"];
       // echo "Post exists ? : " , the_slug_exists($my_post["post_name"]);

 // Insert the post into the database


       $post_id = wp_insert_post( $my_post, true ); // BUG ICII

       if (is_wp_error($post_id)){
        // echo "<br> erreur avec  ", $my_post["post_name"], "<br>" ;
        // echo "<br>---CHAMPS---<br>";
        foreach ($my_post as $key => $value) {
          // echo "<br>", $key, " => ", $value, "<br>" ;
          // code...
        }
      }
       // echo "<br> POST ID : id";
       $post_cat = wp_set_object_terms( $post_id, $category, 'category');
       if (is_wp_error( wp_set_object_terms( $post_id, $category, 'category'))){
         write_log("ERROR ARTICLE : " + $my_post["post_name"]);
         // write_log("ERROR : " + $post_cat->get_error_message());

       }

       if ($type=="Questions") {
         Generate_Featured_Image( "https://www.amplitudeinterim.fr/actus/xactu-39.jpg.pagespeed.ic.puvvpUtkP8.jpg",   $post_id );
       }
       elseif ($rubrique == "ec_echeancier") {
         $image = 'no';

       }
       else {

         Generate_Featured_Image( $file . "/_images/" . $media,   $post_id );

       }
     }
     // echo "unregistered articles : ", $unregistered_articles;
   }



 }










}




        function exploration($rubriques)
        {
            $ftp = "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/";
            foreach ($rubriques as $name) {
                $file = $ftp . $name;



                if ($name == "ec_dessins") {
                    $basePath = $file . "/";
                    // Dessins
                    $fullPath = $basePath . 'ec_flux_dessins.xml';
                    $resultActu = simplexml_load_file($fullPath);


                    $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                    $summary = page_treatment_summary($page);
                    $xmlString = page_treatment($page);


                    treatment($resultActu, $name, $basePath, 'Dessin', $xmlString, "non", $summary);
                } elseif ($name == "ec_metiers") {
                    $basePath = $file . "/Associatifs/tout_flux/";
                    $fullPath = $basePath . 'ec_flux_metiers_associatifs.xml';
                    $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                    $xmlString = page_treatment($page);
                    $summary = page_treatment_summary($page);
                    $resultActu = simplexml_load_file($fullPath);
                    $nameBis = "ec_metiers_associatif";
                    treatment($resultActu, $nameBis, $basePath, 'Actualités', $xmlString, "non", $summary);

                    $basePath = $file . "/Professions_Liberales/tout_flux/";
                    $fullPath = $basePath . 'ec_flux_metiers_professions_liberales.xml';
                    $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                    $xmlString = page_treatment($page);
                    $summary = page_treatment_summary($page);
                    $resultActu = simplexml_load_file($fullPath);
                    $nameBis = "ec_metiers_professions";
                    treatment($resultActu, $nameBis, $basePath, 'Actualités', $xmlString, "non", $summary);

                    $basePath = $file . "/Agricoles/tout_flux/";
                    $fullPath = $basePath . 'ec_flux_metiers_agricoles.xml';
                    $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                    $xmlString = page_treatment($page);
                    $summary = page_treatment_summary($page);
                    $resultActu = simplexml_load_file($fullPath);
                    $nameBis = "ec_metiers_professions";
                    treatment($resultActu, $nameBis, $basePath, 'Actualités', $xmlString, "oui", $summary);
                } elseif ($name == "ec_minute-de-l-expert") {

                    $basePath = $file . "/tout_flux/";
                    $fullPath = $basePath . 'ec_flux_minute_de_l_expert.xml';
                    $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                    $xmlString = page_treatment($page);
                    $summary = page_treatment_summary($page);
                    $resultActu = simplexml_load_file($fullPath);
                    treatment($resultActu, $name, $basePath, 'Interview', $xmlString, "non", $summary);
                } elseif ($name == "ec_echeancier") {

                    $basePath = $file . "/Mois/tout_flux/";
                    $fullPath = $basePath . 'ec_flux_echeancier.xml';
                    $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                    $xmlString = page_treatment($page);
                    $summary = page_treatment_summary($page);
                    $resultActu = simplexml_load_file($fullPath);
                    treatment($resultActu, $name, $basePath, 'Echéancier', $xmlString, "non", $summary);
                } else {




                    if ($name == "ec_social") {

                        $basePath = $file . "/Aides-a-l-embauche/tout_flux/";
                        $fullPath = $basePath . 'ec_flux_aides.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultQuestions = simplexml_load_file($fullPath);
                        treatment($resultQuestions, $name, $basePath, 'Aides à l\'embauche', $xmlString, "non", $summary);

                        $basePath = $file . "/paie/tout_flux/";
                        $fullPath = $basePath . 'ec_flux_paie.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultQuestions = simplexml_load_file($fullPath);
                        treatment($resultQuestions, $name, $basePath, 'Chiffre', $xmlString, "non", $summary);

                        $basePath = $file . "/Actualites/tout_flux/";
                        $fullPath = $basePath . 'ec_flux_actualites.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultActu = simplexml_load_file($fullPath);
                        treatment($resultActu, $name, $basePath, 'Actualités', $xmlString, "non", $summary);


                        $basePath = $file . "/Chiffres-utiles/tout_flux/";
                        $fullPath =  $basePath . 'ec_flux_chiffres.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultChiffresUtiles = simplexml_load_file($fullPath);
                        treatment($resultChiffresUtiles, $name, $basePath, 'Chiffres', $xmlString, "non", $summary);




                        $basePath = $file . "/Dossiers/tout_flux/";
                        $fullPath = $basePath . 'ec_flux_dossiers.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultDossiers = simplexml_load_file($fullPath);
                        treatment($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary);

                        $basePath = $file . "/Questions-reponses/tout_flux/";
                        $fullPath = $basePath . 'ec_flux_faq.xml';
                        $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                        $xmlString = page_treatment($page);
                        $summary = page_treatment_summary($page);
                        $resultQuestions = simplexml_load_file($fullPath);
                        treatment($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary);
                    } else {
                        if ($name == "ec_gestion") {
                            $basePath = $file . "/Dossiers/tout_flux/";
                            $fullPath = $basePath . 'ec_flux_dossiers.xml';
                            $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                            $xmlString = page_treatment($page);
                            $summary = page_treatment_summary($page);
                            $resultDossiers = simplexml_load_file($fullPath);
                            treatment($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary);

                            $basePath = $file . "/Questions-reponses/tout_flux/";
                            $fullPath = $basePath . 'ec_flux_faq.xml';
                            $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                            $xmlString = page_treatment($page);
                            $summary = page_treatment_summary($page);
                            $resultQuestions = simplexml_load_file($fullPath);
                            treatment($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary);
                        } else {

                            if (($name == "ec_patrimoine") || ($name == "ec_multimedia")) {

                                $basePath = $file . "/Actualites/tout_flux/";
                                $fullPath = $basePath . 'ec_flux_actualites.xml';
                                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                                $xmlString = page_treatment($page);
                                $summary = page_treatment_summary($page);
                                $resultActu = simplexml_load_file($fullPath);
                                treatment($resultActu, $name, $basePath, 'Actualités', $xmlString, "non", $summary);




                                $basePath = $file . "/Dossiers/tout_flux/";
                                $fullPath = $basePath . 'ec_flux_dossiers.xml';
                                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                                $xmlString = page_treatment($page);
                                $summary = page_treatment_summary($page);
                                $resultDossiers = simplexml_load_file($fullPath);
                                treatment($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary);

                                $basePath = $file . "/Questions-reponses/tout_flux/";
                                $fullPath = $basePath . 'ec_flux_faq.xml';
                                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                                $xmlString = page_treatment($page);
                                $resultQuestions = simplexml_load_file($fullPath);
                                treatment($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary);
                            } else {




                                // Actualités
                                $basePath = $file . "/Actualites/tout_flux/";
                                $fullPath = $basePath . 'ec_flux_actualites.xml';
                                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                                $xmlString = page_treatment($page);
                                $summary = page_treatment_summary($page);
                                $resultActu = simplexml_load_file($fullPath);
                                treatment($resultActu, $name, $basePath, 'Actualités', $xmlString, "non", $summary);


                                $basePath = $file . "/Chiffres-utiles/tout_flux/";
                                $fullPath =  $basePath . 'ec_flux_chiffres.xml';
                                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                                $xmlString = page_treatment($page);
                                $summary = page_treatment_summary($page);
                                $resultChiffresUtiles = simplexml_load_file($fullPath);
                                treatment($resultChiffresUtiles, $name, $basePath, 'Chiffres', $xmlString, "non", $summary);




                                $basePath = $file . "/Dossiers/tout_flux/";
                                $fullPath = $basePath . 'ec_flux_dossiers.xml';
                                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                                $xmlString = page_treatment($page);
                                $summary = page_treatment_summary($page);
                                $resultDossiers = simplexml_load_file($fullPath);
                                treatment($resultDossiers, $name, $basePath, 'Dossiers', $xmlString, "non", $summary);

                                $basePath = $file . "/Questions-reponses/tout_flux/";
                                $fullPath = $basePath . 'ec_flux_faq.xml';
                                $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                                $xmlString = page_treatment($page);
                                $summary = page_treatment_summary($page);
                                $resultQuestions = simplexml_load_file($fullPath);
                                treatment($resultQuestions, $name, $basePath, 'Questions', $xmlString, "non", $summary);
                            }
                        }
                    }
                }

                if ($name == "ec_social") {

                    $fullPath = "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_loi-de-finances/Fiscalite-personnelle/tout_flux/ec_flux_loi_de_finances_personnelle.xml";
                    $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                    $xmlString = page_treatment_loi_fin($page);
                    $summary = page_treatment_summary($page);

                    $resultActu = simplexml_load_file($fullPath);
                    treatment($resultActu, $name, "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_loi-de-finances/Fiscalite-personnelle", 'Actualités', $xmlString, "ouiSection", $summary);

                    $fullPath = "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_loi-de-finances/Fiscalite-professionnelle/tout_flux/ec_flux_loi_de_finances_professionnelle.xml";
                    $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                    $summary = page_treatment_summary($page);
                    $xmlString = page_treatment_loi_fin($page);
                    $resultActu = simplexml_load_file($fullPath);
                    treatment($resultActu, $name, "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_loi-de-finances/Fiscalite-professionnelle", 'Actualités', $xmlString, "ouiSection", $summary);




                    $fullPath = "ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_tout_flux/ec_flux_faq.xml";
                    $page = htmlspecialchars_decode(htmlentities(file_get_contents($fullPath)));
                    $xmlString = page_treatment($page);
                    $summary = page_treatment_summary($page);
                    $resultActu = simplexml_load_file($fullPath);
                    treatment($resultActu, $name, 'ftp://IM030008V3:F87eRVv4v@ftp.expertinfos.com:21/ec_tout_flux', 'Actualités', $xmlString, "oui", $summary);
                }
            }
        }
        exploration($rubriques);




        //nom de la table

    }
};
$plugin = new RSS_Actu_Plugin();



// page HTML du plugin
function actu_scripts_page()
{
    ?>
    <div class="wrap">
        <h2>Sahar actu plugin</h2>
        <form action="" method="post">
            <?php wp_nonce_field('do_test', '_test_nonce') ?>
            <input type="hidden" name="action" value="start_test">
            <input class="button button-primary" type="submit" value="Start test">
        </form>

        <form action="" method="post">
            <?php wp_nonce_field('do_recup', '_test_nonce') ?>
            <input type="hidden" name="action" value="start_recup">
            <input class="button button-primary" type="submit" value="Récupérer les articles">
        </form>

        <?php
        if (isset($_POST['start_test'])) {


        }
        if (!wp_verify_nonce($_POST['_test_nonce'], 'do_recup')) {
            // error in nonce
        } else {
          echo '<p>Succès!</p>';
          $rubriques = array('ec_dessins', 'ec_fiscal', 'ec_gestion', 'ec_juridique', 'ec_metiers', 'ec_minute-de-l-expert', 'ec_multimedia', 'ec_patrimoine', 'ec_social', 'ec_echeancier');
$plugin = new RSS_Actu_Plugin();
          $plugin->init_data();

        }
        if (!wp_verify_nonce($_POST['_test_nonce'], 'do_test')) {
            // error in nonce
        } else {
            if (start_test()) {

                $articles_array = get_article_count();
                $articles_index = 0;
                $saved_articles = get_category_posts_count(["dessins", "social", "newsletter", "associatif", "fiscal", "gestion", "juridique", "minute", "multimedia", "patrimoine", "professions"]);
                $body = $saved_articles . " articles sauvegardés sur " . count(array_unique($articles_array["article_ids"])) . " disponibles.";
                // Début comptage
                echo '<p>Succès!</p>';
                echo '<p>Resultats: ', $body ,' </p>';
                echo '<p>Articles manquants:</p>';
                echo "<br> number of articles from ftp: ", count(array_unique($articles_array["article_ids"])), "<br>";
                // $vals = array_count_values($articles_array["article_ids"]);
                // echo "<br>duplicates : ", $vals;

                // Fin comptage
                // foreach ($articles_array["article_ids"] as $value) {

                  // if (the_slug_exists($value) == false)
                    // echo "<br>", $value, " does not exist" ;

                // }


                // echo '<p>length titles: ', count($articles_array["article_names"]),'</p>';
                // echo '<p>length IDs: ', count($articles_array["article_ids"]),'</p>';
                // echo '<p>length categories: ', count($articles_array["article_categories"]),'</p>';

             //    if (the_slug_exists("k4_15533762") == false)
             // {
             //   echo '<p>Doesnt exists</p>';
             // }
    global $wpdb;

                foreach ($articles_array["article_names"] as $article_names) {
                  $post_if = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_name like '". $article_names ."'");
                   if ($post_if < 1){

                echo '<p>--------</p><p>Titre: ', $article_names, '</p> <p>ID: ', $articles_array["article_ids"][$articles_index] ,' </p>', '<p>Category: ', $articles_array["article_categories"][$articles_index] ,' </p>', '<p>Publication: ', $articles_array["publication_date"][$articles_index] ,' </p>';
  }
                  $articles_index++;

                  }
  // echo '<p>INDEX : ', $articles_index, '  </p>';
            }
            else {

                echo '<p>Echec!</p>';
            }
        }
            ?>


        </div>

    <?php


}

?>
