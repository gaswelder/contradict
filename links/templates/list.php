<?= tpl('header') ?>

<?php foreach ($groups as $category => $links) : ?>
<h2>{{$category ? $category : 'Other'}}</h2>
<ul>
    <?php foreach ($links as $link) : ?>
    <li>{{date('r', $link->created_at)}} <a href="/links/{{$link->id}}">{{$link->url}}</a></li>
<?php endforeach; ?>
</ul>
<?php endforeach; ?>

<?= tpl('footer') ?>
