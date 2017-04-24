<?php

require __DIR__ .'/../hl/app.php';

$app = new App(__DIR__);

$app->get('/bustrack/', function () {
	if($_SERVER['REQUEST_URI'] == '/bustrack') {
		return Response::redirect('/bustrack/');
	}
    return tpl('index');
});

$app->post('/bustrack/events', function () {
    list($bus, $stop, $time) = array_values(Request::posts('bus', 'stop', 'time'));
    if (!$bus || !$stop || !$time) {
        return Response::BADREQ;
    }

    db()->insert('events', [
        'bus' => $bus,
        'stop' => $stop,
        'time' => $time
    ]);

    return created;
});

$app->get('/bustrack/init', function () {
    $data = [
        'buses' => db()->getValues('select distinct bus from events'),
        'stops' => db()->getValues('select distinct stop from events')
    ];
    return Response::json($data);
});

$app->run();
