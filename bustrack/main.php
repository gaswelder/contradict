<?php

require __DIR__ .'/../hl/app.php';

$app = new App(__DIR__);

$app->get('/bustrack/', function () {
	if($_SERVER['REQUEST_URI'] == '/bustrack') {
		return response::redirect('/bustrack/');
	}
    return tpl('index');
});

$app->post('/bustrack/events', function () {
    list($bus, $stop, $time) = array_values(request::posts('bus', 'stop', 'time'));
    if (!$bus || !$stop || !$time) {
        return response::STATUS_BADREQ;
    }

    db()->insert('events', [
        'bus' => $bus,
        'stop' => $stop,
        'time' => $time
    ]);

    return created;
});

$app->get('/bustrack/events', function() {
	$list = db()->getRecords('select bus, stop, time from events');
	return response::json($list);
});

$app->get('/bustrack/init', function () {
    $data = [
        'buses' => db()->getValues('select distinct bus from events'),
        'stops' => db()->getValues('select distinct stop from events')
    ];
    return response::json($data);
});

$app->run();
