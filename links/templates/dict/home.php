<?= tpl('dict/header') ?>

<a href="/dict/add">Add</a>
<a href="/dict/test">Test</a>

<p>Total: {{$stats['pairs']}}; progress: {{round($stats['progress'] * 100, 1)}} %</p>
