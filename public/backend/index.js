//var tes = "hallo dodik";
//console.log(tes);


var app = require('express')();
var http = require('http').createServer(app);
var io = require('socket.io')(http);
var fs = require('fs');

io.on('connection', function (socket) {
    socket.on("trbListBroadcast", function (data) {
        console.log("Socket Jalan", data);
        
        socket.broadcast.emit("trbListReload", {
            message: data.message
        });
    });
});

http.listen(8080, '0.0.0.0', function () {
    console.log('listening on *:8080');
});
