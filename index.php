<?php

// padrao SR-7
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require "vendor/autoload.php" ;

$app = new \Slim\App([
    "settings" => [ 
        "displayErrorDetails" => true 
    ]
]) ;


//USANDO BANCO DE DADOS COM ILUMINATE   
use Illuminate\Database\Capsule\Manager as Capsule;

//configuracao do banco de dados
$container = $app->getContainer() ;
$container['bd'] = function(){

    $capsule = new Capsule ;

    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'api_teste',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
    ]);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    
    return $capsule ;
};



$app->get("/createBD" , function (Request $request , Response $response){
    
    $bd = $this->get("bd") ; //acessando a variavel bd do container e o metodo schema()
  
    $bd->schema()->dropIfExists("api_teste") ; //criando tabela
    
    $bd->schema()->create("api_teste", function($table){
        $table->increments('id');
        $table->string('cpf');
        $table->string('nome');
    }) ;

    $response->getBody()->write("tabela api_teste criada");

});



$app->get("/[{id}]", function(Request $request, Response $response){
    //listar

    $id = $request->getAttribute("id") ;
    $bd = $this->get("bd") ; //acessando a variavel bd do container e o metodo schema()
    
    $data = $bd->table("api_teste")->get() ;

    if( $id != ""){
        $data = $bd->table("api_teste")->where("id",$id)->get() ;
    }

    $response = $response->withHeader('Content-Type', 'application/json');  
    $response->getBody()->write(json_encode($data));
});    

$app->post("/post", function(Request $request, Response $response){
    
    $post = $request->getParsedBody() ;
    
    //inserindo dados recebidos via pos em variaveis
    $cpf = $post['cpf'] ;
    $nome = $post['nome'] ;

    //logica insert
    $bd = $this->get("bd") ; //acessando a variavel bd do container e o metodo schema()
    $bd->table("api_teste")->insert([
        //id auto incremento

        "cpf" => $cpf,
        "nome" => $nome
    ]);
});



$app->delete("/delete/{id}",function(Request $request, Response $response){
    //delete
    $bd = $this->get("bd") ; //acessando a variavel bd do container e o metodo schema()

    $id = $request->getAttribute("id") ;

    $bd->table("api_teste")->where("id",$id)->delete(); 
});



$app->put("/put/{id}", function(Request $request, Response $response, $args){
    //atualizando (put)
    
    $id = $request->getAttribute("id") ;
    $post = $request->getParsedBody() ;
    $cpf = $post['cpf'] ;
    $nome = $post['nome'] ;
    
    $bd = $this->get("bd") ; //acessando a variavel bd do container e o metodo schema()
    
    $bd->table("api_teste")->where("id",$id)->update([
        //id auto incremento
        "cpf" => $cpf,
        "nome" => $nome,
    ]);

});

$app->run() ;

?>