<?php
require 'vendor/autoload.php';

use controllers\CategoryController;
use controllers\departmentController;
use controllers\IndexController;
use controllers\CreateItemController;
use db\connection;

use models\Annonce;
use models\Categorie;
use models\Annonceur;
use models\Departement;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;


connection::createConn();

// Initialisation de Slim
$app = new App([
    'settings' => [
        'displayErrorDetails' => true,
    ],
]);

// Initialisation de Twig
$loader = new FilesystemLoader(__DIR__ . '/template');
$twig   = new Environment($loader);

// Ajout d'un middleware pour le trailing slash
$app->add(function (Request $request, Response $response, $next) {
    $uri  = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && str_ends_with($path, '/')) {
        $uri = $uri->withPath(substr($path, 0, -1));
        if ($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        } else {
            return $next($request->withUri($uri), $response);
        }
    }
    return $next($request, $response);
});


if (!isset($_SESSION)) {
    session_start();
    $_SESSION['formStarted'] = true;
}

if (!isset($_SESSION['token'])) {
    $token                  = md5(uniqid(rand(), TRUE));
    $_SESSION['token']      = $token;
    $_SESSION['token_time'] = time();
} else {
    $token = $_SESSION['token'];
}

$menu = [
    [
        'href' => './IndexController.php',
        'text' => 'Accueil'
    ]
];

$chemin = dirname($_SERVER['SCRIPT_NAME']);

$cat = new CategoryController();
$dpt = new departmentController();

$app->get('/', function () use ($twig, $menu, $chemin, $cat) {
    $index = new IndexController();
    $index->displayAllAnnonce($twig, $menu, $chemin, $cat->getCategories());
});

$app->get('/itemController/{n}', function ($request, $response, $arg) use ($twig, $menu, $chemin, $cat) {
    $n     = $arg['n'];
    $item = new CreateItemController();
    $item->afficherItem($twig, $menu, $chemin, $n, $cat->getCategories());
});

$app->get('/add', function () use ($twig, $app, $menu, $chemin, $cat, $dpt) {
    $ajout = new controllers\CreateItemController();
    $ajout->addItemView($twig, $menu, $chemin, $cat->getCategories(), $dpt->getAllDepartments());
});

$app->post('/add', function ($request) use ($twig, $app, $menu, $chemin) {
    $allPostVars = $request->getParsedBody();
    $ajout       = new controllers\CreateItemController();
    $ajout->addNewItem($twig, $menu, $chemin, $allPostVars);
});

$app->get('/itemController/{id}/edit', function ($request, $response, $arg) use ($twig, $menu, $chemin) {
    $id   = $arg['id'];
    $item = new CreateItemController();
    $item->modifyGet($twig, $menu, $chemin, $id);
});
$app->post('/itemController/{id}/edit', function ($request, $response, $arg) use ($twig, $app, $menu, $chemin, $cat, $dpt) {
    $id          = $arg['id'];
    $allPostVars = $request->getParsedBody();
    $item        = new CreateItemController();
    $item->modifyPost($twig, $menu, $chemin, $id, $allPostVars, $cat->getCategories(), $dpt->getAllDepartments());
});

$app->map(['GET, POST'], '/itemController/{id}/confirm', function ($request, $response, $arg) use ($twig, $app, $menu, $chemin) {
    $id   = $arg['id'];
    $allPostVars = $request->getParsedBody();
    $item        = new CreateItemController();
    $item->edit($twig, $menu, $chemin, $id, $allPostVars);
});

$app->get('/search', function () use ($twig, $menu, $chemin, $cat) {
    $s = new controllers\SearchController();
    $s->show($twig, $menu, $chemin, $cat->getCategories());
});


$app->post('/search', function ($request, $response) use ($app, $twig, $menu, $chemin, $cat) {
    $array = $request->getParsedBody();
    $s     = new controllers\SearchController();
    $s->research($array, $twig, $menu, $chemin, $cat->getCategories());

});

$app->get('/annonceur/{n}', function ($request, $response, $arg) use ($twig, $menu, $chemin, $cat) {
    $n         = $arg['n'];
    $annonceur = new controllers\AdvertiserViewController();
    $annonceur->afficherAnnonceur($twig, $menu, $chemin, $n, $cat->getCategories());
});

$app->get('/del/{n}', function ($request, $response, $arg) use ($twig, $menu, $chemin) {
    $n    = $arg['n'];
    $item = new controllers\CreateItemController();
    $item->supprimerItemGet($twig, $menu, $chemin, $n);
});

$app->post('/del/{n}', function ($request, $response, $arg) use ($twig, $menu, $chemin, $cat) {
    $n    = $arg['n'];
    $item = new controllers\CreateItemController();
    $item->supprimerItemPost($twig, $menu, $chemin, $n, $cat->getCategories());
});

$app->get('/cat/{n}', function ($request, $response, $arg) use ($twig, $menu, $chemin, $cat) {
    $n = $arg['n'];
    $categorie = new controllers\CategoryController();
    $categorie->displayCategorie($twig, $menu, $chemin, $cat->getCategories(), $n);
});

$app->get('/api(/)', function () use ($twig, $menu, $chemin, $cat) {
    $template = $twig->load('api.html.twig');
    $menu     = array(
        array(
            'href' => $chemin,
            'text' => 'Acceuil'
        ),
        array(
            'href' => $chemin . '/api',
            'text' => 'Api'
        )
    );
    echo $template->render(array('breadcrumb' => $menu, 'chemin' => $chemin));
});

$app->group('/api', function () use ($app, $twig, $menu, $chemin, $cat) {

    $app->group('/annonce', function () use ($app) {

        $app->get('/{id}', function ($request, $response, $arg) use ($app) {
            $id          = $arg['id'];
            $annonceList = ['id_annonce', 'id_categorie as categorie', 'id_annonceur as annonceur', 'id_departement as departement', 'prix', 'date', 'titre', 'description', 'ville'];
            $return      = Annonce::select($annonceList)->find($id);

            if (isset($return)) {
                $response->headers->set('Content-Type', 'application/json');
                $return->categorie     = Categorie::find($return->categorie);
                $return->annonceur     = Annonceur::select('email', 'nom_annonceur', 'telephone')
                    ->find($return->annonceur);
                $return->departement   = Departement::select('id_departement', 'nom_departement')->find($return->departement);
                $links                 = [];
                $links['self']['href'] = '/api/annonce/' . $return->id_annonce;
                $return->links         = $links;
                echo $return->toJson();
            } else {
                $app->notFound();
            }
        });
    });

    $app->group('/annonces(/)', function () use ($app) {

        $app->get('/', function ($request, $response) use ($app) {
            $annonceList = ['id_annonce', 'prix', 'titre', 'ville'];
            $response->headers->set('Content-Type', 'application/json');
            $a     = Annonce::all($annonceList);
            $links = [];
            foreach ($a as $ann) {
                $links['self']['href'] = '/api/annonce/' . $ann->id_annonce;
                $ann->links            = $links;
            }
            $links['self']['href'] = '/api/annonces/';
            $a->links              = $links;
            echo $a->toJson();
        });
    });


    $app->group('/categorie', function () use ($app) {

        $app->get('/{id}', function ($request, $response, $arg) use ($app) {
            $id = $arg['id'];
            $response->headers->set('Content-Type', 'application/json');
            $a     = Annonce::select('id_annonce', 'prix', 'titre', 'ville')
                ->where('id_categorie', '=', $id)
                ->get();
            $links = [];

            foreach ($a as $ann) {
                $links['self']['href'] = '/api/annonce/' . $ann->id_annonce;
                $ann->links            = $links;
            }

            $c                     = Categorie::find($id);
            $links['self']['href'] = '/api/categorie/' . $id;
            $c->links              = $links;
            $c->annonces           = $a;
            echo $c->toJson();
        });
    });

    $app->group('/categories(/)', function () use ($app) {
        $app->get('/', function ($request, $response, $arg) use ($app) {
            $response->headers->set('Content-Type', 'application/json');
            $c     = Categorie::get();
            $links = [];
            foreach ($c as $cat) {
                $links['self']['href'] = '/api/categorie/' . $cat->id_categorie;
                $cat->links            = $links;
            }
            $links['self']['href'] = '/api/categories/';
            $c->links              = $links;
            echo $c->toJson();
        });
    });

    $app->get('/key', function () use ($app, $twig, $menu, $chemin, $cat) {
        $kg = new controllers\KeyGeneratorController();
        $kg->show($twig, $menu, $chemin, $cat->getCategories());
    });

    $app->post('/key', function () use ($app, $twig, $menu, $chemin, $cat) {
        $nom = $_POST['nom'];

        $kg = new controllers\KeyGeneratorController();
        $kg->generateKey($twig, $menu, $chemin, $cat->getCategories(), $nom);
    });
});


$app->run();
