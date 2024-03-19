<?php

//Função que converte os parametros de requisições HTTP POST e PUT.



function f_parametro_to_habito()
{
    //Obtem o conteúdo da requisição
    $dados = file_get_contents("php://input");

    //Converte para json e retornar
    $habito = json_decode($dados, true);
    return $habito;


}


function f_obtem_conexao()
{

    
    //informações sobre o banco de dados
    $servidor = "localhost";
    $usuario = "root";
    $senha = "root";
    $bancodedados="listadehabito";

    //Cria uma conexão
    $conexao = new mysqli ($servidor, $usuario, $senha, $bancodedados);

    //Verificar a conexão foi
    if($conexao -> connect_error)
    {
        die("Falha na conexão: " . $conexao->connect_error);
    }

    return $conexao;
}


function f_select_habito()
{
    //cria uma clásula WHERE com os parâmetros que foram recebidos através da reuisição
    // HTTP get


    //$_GET armazena em um array os parametro passados via get na requisição. Exemplo:
// http://localhost:80/lista-de-habitos-rest-api/habito.php?id=2&status=A  irá resultar
// em um array ([id] =>2 [status]=> A)


// array_keys() é uma função que retorna um novo array que cujos os elemetos são os indices
// do array recebido. Tomando como exemplo o array anterior :
// array ([0] =>id [1]=> status)





//json_Encode: converte para json o objeto ou array

    $queryWhere = " WHERE ";
    $primeiroParametro = true;
    $parametrosGet = array_keys($_GET);
    foreach($parametrosGet as $param)
    {
        if(!$primeiroParametro)
        { 
            $queryWhere .= " AND ";
        }

        $primeiroParametro = false;
        $queryWhere .= $param." = '".$_GET[$param]."'";
    }


    //Executa a query da variável $sql
    $sql = "SELECT id, nome, status FROM habito";

    //utiliza o where criado com base no parâmetros do GET
    if($queryWhere != " WHERE ")
    {
        $sql .= $queryWhere;
    }

    $conexao = f_obtem_conexao();

    //Executa a query
    $resultado = $conexao->query($sql);

    // Verifica se a query retornou registros
    if($resultado->num_rows > 0)
    {

        //Inicializa o array para a formação dos objetos JSON
        $jsonHabitoArray = Array();
        $contador = 0;
        while($registro = $resultado->fetch_assoc())
        {
            //Monta um objeto Json através de um array associativo
            $jsonHabito = Array();
            $jsonHabito["id"] = $registro["id"];
            $jsonHabito["nome"] = $registro["nome"];
            $jsonHabito["status"] = $registro["status"];
            $jsonHabitoArray[$contador++] = $jsonHabito;
        }

        //Transforma o Array com os resultados da query em um array Json e imprime-o
        //na página
        echo json_encode($jsonHabitoArray);

    }
    else
    {
        //Se query não retornou devolve um arrayJsin vazio
        echo json_encode(Array());
    }


    //fecha a conexão com o mysql
    $conexao ->close();

}













function f_insert_habito()
{
    $habito = f_parametro_to_habito();

    //Busca o nome que foi recebido via post através do formulario de cadastro
    $nome = $habito["nome"];

    //Insere o habito na tabela habito do banco de dados
    $sql = "INSERT INTO habito (nome) VALUES ('".$nome."')";

    $conexao = f_obtem_conexao();


    if(!($conexao->query($sql) === TRUE))
    {
        $conexao->close();
        die("Erro: " .$sql."<br>".$conexao->error);
    }

    //Insere as demais informações no json

    $habito["id"] = mysqli_insert_id($conexao);
    $habito["status"] = "A";

    echo json_encode($habito);

    //fecha a conexão
    $conexao->close();
}













function f_update_habito()
{
    $habito = f_parametro_to_habito();


    $id = $habito["id"];
    $nome = $habito["nome"];
    $status = $habito["status"];


    //atualiza o status de A (ativo) para V(vencido)
    $sql = "UPDATE habito SET status = '".$status."', nome = '".$nome."' WHERE id= ".$id;

    //Obtém conexão com o banco de dados
    $conn = f_obtem_conexao();

    //Verifica se o comando foi executado com sucesso
    if(!($conn->query($sql) === TRUE))
    {
        $conn->close();
        die("Erro ao atualizar: " .$conn->error);
    }


    //retorna o Registro atualizado
    echo json_encode($habito);

    $conn->close();

}











function f_delete_habito()
{

    //recebe o id so registro que foi recebido via get
    $id = $_GET["id"];

    $sql = "DELETE FROM habito WHERE id=".$id;

    $conn = f_obtem_conexao();

    if(!($conn->query($sql) === TRUE))
    {
        die("erro ao deletar:".$conn->error);
    }
    $conn->close();
}













// A variavel de servidor REQUEST_METHOD conteem o nome do metodo HTTP através qual
//o arquivo  solicitado foi acessadi

$metodo = $_SERVER['REQUEST_METHOD'];

//verifica qual ação foi tomada de acordo com o metodo http que foi utilizado para acessar
// este recurso

switch($metodo){

    case "GET":
        f_select_habito();
        break;

    case "POST":
        f_insert_habito();
        break;
    
    case "PUT":
        f_update_habito();
        break;

    case "DELETE":
        f_delete_habito();
        break;

}

// http://localhost:80/lista-de-habitos-rest-api

?>