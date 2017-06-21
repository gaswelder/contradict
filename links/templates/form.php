<?= tpl('header') ?>

    <h1>New link</h1>

    <form method="post" action="/links/">
        <datalist id="categories">
            <?php foreach ($categories as $cat) : ?>
                <option value="{{$cat}}">
            <?php endforeach; ?>
        </datalist>
        <div>
            <label>URLs</label>
            <textarea name="url" required autofocus></textarea>
        </div>
        <div>
            <label>Category</label>
            <input list="categories" name="category">
        </div>
        <button type="submit">Save</button>
    </form>

<?= tpl('footer') ?>
