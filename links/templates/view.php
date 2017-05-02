<?= tpl('header') ?>

    <h1>{{$title}}</h1>

    <p>Direct link: <a href="{{$link->url}}">{{$link->url}}</a></p>

    <form method="post" action="/links/{{$link->id}}/action">
        <button type="submit" name="act" value="archive">Archive</button>
        <button type="submit" name="act" value="later">Put for later</button>
    </form>

    <form method="post" action="/links/{{$link->id}}/category">
        <datalist id="categories">
            <?php foreach ($categories as $cat) : ?>
                <option value="{{$cat}}">
            <?php endforeach; ?>
        </datalist>
        <div>
            <label>Category</label>
            <input list="categories" name="category" value="{{$link->category}}">
        </div>
        <button type="submit">Save</button>
    </form>
<?= tpl('footer') ?>
