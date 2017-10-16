<?= tpl('header') ?>

<?php
$groups = [];
foreach ($links as $link) {
    $groups[$link->category][] = $link;
}
uasort($groups, function ($a, $b) {
    return count($b) - count($a);
});
?>

<?php foreach ($groups as $category => $links) : ?>
<a href="/links/category/{{alt(urlencode(str_replace('/', ':', $category)), 'other')}}"><h2>{{$category ? $category : 'Other'}}</h2></a>
<ul>
    <?php foreach ($links as $link) : ?>
    <li>{{date('Y-m-d', $link->created_at)}} <a href="/links/{{$link->id}}">{{$link->url}}</a></li>
<?php endforeach; ?>
</ul>
<?php endforeach; ?>

<?= tpl('footer') ?>
