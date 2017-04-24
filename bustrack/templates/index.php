<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="res/debug.js"></script>

    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.2/css/materialize.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.2/js/materialize.min.js"></script>

</head>

<body>
    <script src="https://unpkg.com/vue"></script>

    <div id="app" class="container">
        <form v-on:submit.prevent="submit">
            <div>
                <label>Bus</label>
                <input v-model="bus" list="buses" required>
                <datalist id="buses">
                <option v-for="bus in buses" v-bind:value="bus">
            </datalist>
            </div>
            <div>
                <label>Stop</label>
                <input v-model="stop" list="stops" required>
                <datalist id="stops">
                <option v-for="stop in stops" v-bind:value="stop">
            </datalist>
            </div>
            <div>
                <label>Time</label>
                <input readnoly v-bind:value="time">
            </div>
            <div>
                <input type="checkbox" v-model="freeze" id="freeze-switch">
                <label for="freeze-switch">Freeze time</label>
            </div>
            <div class="fixed-action-btn">
                <button type="submit" v-bind:disabled="sending" class="btn-floating btn-large red waves-effect waves-light">Save</button>
            </div>
        </form>
    </div>
    <script src="res/runtime.js"></script>
    <script src="res/main.js"></script>
</body>

</html>
