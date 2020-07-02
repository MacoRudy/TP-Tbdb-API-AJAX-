<?php

namespace App\Controller;

use App\Entity\Movie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TmdbController extends AbstractController
{
    /**
     * @Route("/tmdb", name="tmdb")
     */
    public function index()
    {
        // recuperer le contenu d'une url
        // file_get_contents($url);

        $startAtPage = 1;
        for ($i = $startAtPage; $i <= ($startAtPage + 10); $i++) {
            $this->getMoviesFromTbdb($i);
        }

        return new Response("done");
    }

    private function getMoviesFromTbdb(int $page = 1)
    {
        // crée un client HTTP capable de faire des requetes HTTP
        $client = HttpClient::create();

        $url = "https://api.themoviedb.org/3/discover/movie?api_key=f4cdc85408d87dd72a6b81a15f56a31c&language=en-US&sort_by=vote_average.desc&page=1&vote_count.gte=10000&page=$page";
        // déclenche la requete a l'API de TMDB
        $reponse = $client->request('GET', $url);
        $content = $reponse->toArray();

        $em = $this->getDoctrine()->getManager();
        $movieRepo = $this->getDoctrine()->getRepository(Movie::class);
        foreach ($content['results'] as $movieData) {

            $foundExistingMovie = $movieRepo->findOneBy(['tmdbId' => $movieData['id']]);

            if ($foundExistingMovie) {
                echo "existe deja <br>";
                continue;
            }
            $movie = new Movie();
            $movie->setTitle($movieData['original_title']);
            $movie->setPoster($movieData['poster_path']);
            $movie->setTmdbId($movieData['id']);
            $em->persist($movie);
        }
        $em->flush();

    }


}
