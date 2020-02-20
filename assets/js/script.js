$(document).ready(function(){
    //make a connection        
    var conn = new WebSocket('ws://localhost:8080');

    conn.onopen = function(e){
        $('.status').html('Status: You\'re connected<br>')
    }

    //$('.click').click(function(e){
        //let message = e.data;
        //let message = $('.mocker').val();
    conn.onmessage = function(e){
        message = e.data;
        console.log(message);
        switch(true){
            case Array.isArray(message.match(/\{\"names\":/g)): indexPage(JSON.parse('' + message));
            break;
            default : boardPage(JSON.parse('' + message));
        }
    }

    function indexPage(message){
        //if necessary
        exit_board();

        //call update player list
        update_player_list(message['names']);

        //call board update
        update_board_list(message['avaliable_board']);
    }

    function boardPage(message){

        //show board
        show_board([message['board']['x'], message['board']['o']]);

        if(message['log'])
        $('.message').append(message['log'] + '<br>');

        //set player x name
        if(message['players']['x']['name'])
            $('.player-x').val(message['players']['x']['name']);
        else
            $('.player-x').val('click to sit for X');

        //set player x name
        if(message['players']['o']['name'])
            $('.player-o').val(message['players']['o']['name']);
        else
            $('.player-o').val('click to sit for O');

        (message['can_ready']['x'] || message['can_ready']['o']) ? $('.get-ready').show() : $('.get-ready').hide();

        (message['can_exit'] ? $('.exit-board').show() : $('.exit-board').hide());

        //attach a click event
        $('td > a').click(function(){
            if(!message['can_play'])
                return false;

            move = 'b-m-' + $(this).attr('id');
            conn.send(move);
        })
    }

    function update_player_list(message){
        let playersname = message
        //clear tag
        $('.online-players').html('');
        
        //fill the list
        for(let id in playersname) {
            $('.online-players').append('<li class="list-group-item"><span class="player-names">' + playersname[id] +' </span></li>');
        };

        
    }

    function update_board_list(message){
        $('.available-board').html('');

        for(let id in message)
            $('.available-board').append('<li class="list-group-item"><span class="board-on-line"> Board - ' + message[id] +'&nbsp; &nbsp; &nbsp; </span> <input type="button" value="Enter" id="board-'+ message[id] +'" class="btn btn-danger board-button"></li>');
    }

    function show_board(moves){
        //hide board list
        $('.playerList').hide();
        $('.boardList').hide();

        //hide create button
        $('.newBoard').hide();

        //show board
        $('.board').show();
        $('.message').show();

        x = moves[0];
        o = moves[1];

        //paint board
        xBoxes = bit2array(x);
        oBoxes = bit2array(o);

        $('td>a').each(function(){
            $(this).html('-');
        })

        xBoxes.forEach(element => {
            $('.box-' + element).html('X');
        });

        oBoxes.forEach(element => {
            $('.box-' + element).html('O');
        });

    }

    function exit_board(){
        //show board and player list
        $('.playerList').show();
        $('.boardList').show();

        //show create button
        $('.newBoard').show();

        //show board
        $('.board').hide();

        $('.message').html('');
    }

    function bit2array(bitNum){
        let arraybit = [];
        for (let index = 0; index < 9; index++) {
            index2power = Math.pow(2, index);
            if((bitNum & index2power) == index2power)
                   arraybit.push(index + 1);
        }
    
        return arraybit;
    }

    /**
     * CONTROLLERS
     */

    //hide board on starting
    $('.board').hide();

    //Event handler
    $('.newBoard').click(function(){
        conn.send('b-create');
    })

    $('.exit-board').click(function(){
        conn.send('exit-board');
    })

    $('.get-ready').click(function(){
        conn.send('b-ready');
    });

    $('.available-board').on('click', '.board-button', function(){
        let htmlid = $(this).attr('id'), splitted = htmlid.split("-")
        let id = splitted[1];
        conn.send('add-to-board-' + id);
    })

    $('.player-x').click(function(){
        conn.send('b-sit-1');
    })

    $('.player-o').click(function(){
        conn.send('b-sit-0')
    })
});