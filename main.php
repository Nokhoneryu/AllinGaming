<?php
/**
 * Created by PhpStorm.
 * User: José Miguel
 * Date: 27/09/2016
 * Time: 00:06
 */

if( !isset($_SESSION)){session_start();}

require('vendor/autoload.php');
require('connections/connection.php');


/**BATTLE.NET OR BLIZZARD ACCOUNT OR WHATEVER THEY'RE CHANGING INTO**/
/**-----------------------------------GRACIAS ULMINIA!!--------------------------**/

require('includes/blizzard-api-oauth-master/Client.php');
require('includes/blizzard-api-oauth-master/GrantType/IGrantType.php');
require('includes/blizzard-api-oauth-master/GrantType/AuthorizationCode.php');


?>
    <form action="?loginBattle" method="post">
        <input type="image" src="https://us.battle.net/mashery-assets/static/images/battlenet-logo.png">
    </form>

<?php




if(isset($_GET['loginBattle'])) {


    $client_id = 'c7ecz58d75qgq3fb3wu6rg9spyn7e5z5';
    $client_secret = 'awt7GCwyPNCqp9SpWtfYN2dKfPaKcSf9';
    $region = 'EU';
    $locale = 'pt';
    $redirect_uri = 'https://localhost/Lab5/SteamBattleNet/index.php';

    $Bclient = new OAuth2\Client($client_id, $client_secret, $region, $locale, $redirect_uri);


    if (!isset($_GET['code'])) {
        $auth_url = $Bclient->getAuthenticationUrl($Bclient->baseurl[$Bclient->region]['AUTHORIZATION_ENDPOINT'], $Bclient->redirect_uri);
        header('Location: ' . $auth_url);
        die('Redirect');
    } else {
        $params = array('code' => $_GET['code'], 'auth_flow' => 'auth_code', 'redirect_uri' => $Bclient->redirect_uri);
        $response = $Bclient->getAccessToken($Bclient->baseurl[$Bclient->region]['TOKEN_ENDPOINT'], 'authorization_code', $params);
        $Bclient->setAccessToken($response['result']['access_token']);
        $response = $Bclient->fetch('user', array('source' => 'account'));
        echo '<pre>';
        print_r($response);
        echo '</pre>';
        $_POST['user'] = $response;
    }
}

if(isset($_GET['id'])){
    $email=$_SESSION["email"];
    $battleName=$_GET['name']."#".$_GET['ref'];
    $battleid=$_GET['id'];

    $queryB = "UPDATE utilizadores SET battleTag = ?, battleName = ? WHERE email = ?";
    $stmtB = mysqli_prepare($connect, $queryB);
    mysqli_stmt_bind_param($stmtB, 'sss', $battleid, $battleName, $email);
    mysqli_stmt_execute($stmtB);
    mysqli_stmt_close($stmtB);
}
//exemplo de query de info
//$r = $Bclient->fetch('user',array('name'=>'ulminia','server'=>'zangarmarsh','fields'=>'items,stats'));
//echo '<pre>';
//print_r($r);
//echo '</pre>';
/**----------------------------FIM OAUTH2 BATTLENET-----------------------------*/






/**STEAM**/
/**----------------------------baseado no exemplo do LightOpenId-----------------------------*/
http://steamcommunity.com/openid
require 'includes/lightopenid/openid.php';

$Sclient = new \Zyberspace\SteamWebApi\Client('45FF2974E9ED6B469B97294BC24666AC');

$_STEAMAPI = "45FF2974E9ED6B469B97294BC24666AC";

try
{
    $openid = new LightOpenID('http://localhost/Lab5/SteamBattleNet/main.php');
    if(!$openid->mode)
    {
        if(isset($_GET['loginSteam']))
        {
            $openid->identity = 'http://steamcommunity.com/openid';
            header('Location: ' . $openid->authUrl());
        }
        ?>
        <form action="?loginSteam" method="post">
            <input type="image" src="https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_01.png">
        </form>
        <?php
    }
    elseif($openid->mode == 'cancel')
    {
        echo 'Login cancelado';
    }
    else
    {
        if($openid->validate())
        {
            $id = $openid->identity;
            // identity is something like: http://steamcommunity.com/openid/id/76561197960435530
            // we only care about the unique account ID at the end of the URL.
            $ptn = "/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
            preg_match($ptn, $id, $matches);
            echo "Login bem sucedido (steamID: $matches[1])\n";

            $url = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=$_STEAMAPI&steamids=$matches[1]";
            $json_object= file_get_contents($url);
            $json_decoded = json_decode($json_object);

            foreach ($json_decoded->response->players as $player)

                $steamid=$player->steamid;
            {
                echo "
                    <br/>Player ID: $player->steamid
                    <br/>Player Name: $player->personaname
                    <br/>Profile URL: $player->profileurl
                    <br/>SmallAvatar: <img src='$player->avatar'/>
                    <br/>MediumAvatar: <img src='$player->avatarmedium'/>
                    <br/>LargeAvatar: <img src='$player->avatarfull'/>
                    ";
            }

            $steamName=$player->personaname;
            $steamImg=$player->avatarfull;

            $email=$_SESSION["email"];

            $queryS = "UPDATE utilizadores SET steamID = ?, steamName = ?, steamImg = ? WHERE email = ?";
            $stmtS = mysqli_prepare($connect, $queryS);
            mysqli_stmt_bind_param($stmtS, 'ssss', $steamid, $steamName, $steamImg, $email);
            mysqli_stmt_execute($stmtS);
            mysqli_stmt_close($stmtS);

        }
        else
        {
            echo "Utilizador sem Login efetuado.\n";
        }
    }
}
catch(ErrorException $e)
{
    echo $e->getMessage();
}

/**----------------------------FIM LIGHTOPENID STEAM-----------------------------*/


/**---------------------------FACEBOOK E GOOGLE---------------------------------*/

require_once('vendor/facebook/graph-sdk/src/Facebook/autoload.php');

?>

    <form action="?loginFacebook" method="post">
        <input type="image" src="https://z-m-scontent-lhr.xx.fbcdn.net/t39.2178-6/851579_209602122530903_1060396115_n.png?_nc_ad=z-m">
    </form>

    <form action="?loginGoogle" method="post">
        <input type="image" src="assets/signin-assets/web/2x/btn_google_signin_dark_normal_web@2x.png">
    </form>
<?php

if (isset($_GET['loginFacebook'])){

    $config = array(
        "base_url" => "https://localhost/Lab5/SteamBattleNet/includes/hybridauth-2.8.0/hybridauth/index.php",
        "providers" => array (
            "Facebook" => array (
                "enabled" => true,
                "keys"    => array ( "id" => "1192993594117448", "secret" => "8b731ede8cb547a62757963596c10d4e" ),
                "scope"   => "email, user_about_me, user_birthday, user_hometown", // optional
                "display" => "popup"
            )));

    require_once( "includes/hybridauth-2.8.0/hybridauth/Hybrid/Auth.php" );

    $hybridauth = new Hybrid_Auth( $config );

    $adapter = $hybridauth->authenticate( "Facebook" );

    $user_profile = $adapter->getUserProfile();

//    para mostrar o recebido
//    var_dump($user_profile);

    $nome=$user_profile->displayName;
    $email=$user_profile->emailVerified;
    $img=$user_profile->photoURL;
    $idade=$user_profile->age;
    $genero=$user_profile->gender;

    if (is_null($idade)){
        //A culpa disto é do Fábio!
        $idade="";
    }
    if (is_null($genero)){
        $genero="";
    }

    $query = "INSERT INTO utilizadores(nome,email,idade,genero,imgPerfil) VALUES(?,?,?,?,?)";
    $user_insert = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($user_insert, 'sssss', $nome, $email,$idade,$genero, $img);
    if(mysqli_stmt_execute($user_insert)){
        $_SESSION["username"]=$nome;
        $_SESSION["email"]=$email;
    };
    mysqli_stmt_close($user_insert);


}

if (isset($_GET['loginGoogle'])){

    $config = array(
        "base_url" => "https://localhost/Lab5/SteamBattleNet/includes/hybridauth-2.8.0/hybridauth/index.php",
        "providers" => array (
            "Google" => array (
                "enabled" => true,
                "keys" => array("id" => "297939951141-og62mhv97f776puvjics1o1kq9qi4hoa.apps.googleusercontent.com", "secret" => "f-MK8SfZ5Q2TIQsAfSVo6wtY"),
                "scope"=>"email"
            )));

    require_once( "includes/hybridauth-2.8.0/hybridauth/Hybrid/Auth.php" );

    $hybridauth = new Hybrid_Auth( $config );

    $adapter = $hybridauth->authenticate( "Google" );

    $user_profile = $adapter->getUserProfile();

//    para mostrar o recebido
//    var_dump($user_profile);

    $nomeG=$user_profile->displayName;
    $emailG=$user_profile->emailVerified;
    $imgG=$user_profile->photoURL;
    $idadeG=$user_profile->age;
    $generoG=$user_profile->gender;

    if (is_null($idadeG)){
        $idade="0";
    }
    if (is_null($generoG)){
        $genero="";
    }

    $queryGoogle = "INSERT INTO utilizadores(nome,email,idade,genero,imgPerfil) VALUES(?,?,?,?,?)";
    $user_insertG = mysqli_prepare($connect, $queryGoogle);
    mysqli_stmt_bind_param($user_insertG, 'sssss', $nomeG, $emailG,$idadeG,$generoG, $imgG);
    if(mysqli_stmt_execute($user_insertG)){
        $_SESSION["username"]=$nomeG;
        $_SESSION["email"]=$emailG;
    };
    mysqli_stmt_close($user_insertG);

}