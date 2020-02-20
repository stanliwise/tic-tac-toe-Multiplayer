<?php
    use Ratchet\Server\IoServer;
    use Ratchet\Http\HttpServer;
    use Ratchet\WebSocket\WsServer;
    use MyApp\Chat;

    $rootDirectory = __DIR__;
    
    require __DIR__ .'/vendor/autoload.php';
    require $rootDirectory . '/models/player.php';
    require $rootDirectory . '/models/Board.php';
    require $rootDirectory . '/models/player_board.php';
    require $rootDirectory . '/models/Registrar.php';
    require $rootDirectory . '/controller/Game_controller.php';
    require $rootDirectory . '/Chat.php';

    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Chat()
            )
        ), 
        8080
    );

    $server->run();