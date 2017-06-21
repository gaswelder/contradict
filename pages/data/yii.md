## What it is

It is a combination of an MVC implementation, PHP and JS libraries and a possibility of installing extensions.

MVC is a convention to put page templates into "views" directory, data classes into "models" directory, and the rest into "controllers" directory. A controller typically takes into account request data, gets data using model classes, decides which view template will be used, and passes the data to that view template.


## Installation

composer create-project --prefer-dist yiisoft/yii2-app-basic <dirname>


## Running

An application object is created, and its 'run' function is called. The constructor takes a $config argument.

	// load application configuration
	$config = require(__DIR__ . '/../config/web.php');

	// instantiate and configure the application
	(new yii\web\Application($config))->run();

There are two required configuration keys: `id` and `basePath`. `id` is just a unique string. `basePath` is normally `dirname(__DIR__)`, the directory of the whole setup. The '@app' alias in other places refers to this path.


## Routes

When processing a request, the URL is converted to a "route". The route specifies which module to look in, which controller to create in that module, and which function (action) to call in it.

If a route has two parts, like 'post/view', then it specifies controller and action. If it has three parts, like 'forum/post/view', then it specifies module, controller, and action.

URLs are created from routes and query arguments. To create a URL the \yii\helpers\Url object is used:

	// route is 'post/view', query string is 'id=100'.
	$url = \yii\helpers\Url::to(['post/view', 'id' => 100]);


## Changing the URL scheme

The mapping is performed by the urlManager object. Its settings may be changed in `config/web.php`. The `enablePrettyUrl` enables human-friendly URL format.

The `defaultRoute` specifies the "home page" route.

The manager operates on "url rules". A rule is a combination of pathinfo pattern, a route, and query parameters. When converting URLs to routes the first rule with matching pathinfo pattern is used. When converting routes to URLs the first rule with matching route and query parameters is used.

Custom rules are specified in the `rules` property which is a map of rules as (path pattern => route), for example:

	[
		'posts' => 'post/index',
		'post/new' => 'post/new'
	]

Path patterns may also have full form, with protocol and DNS names:

	[
		'http://admin.example.net/login' => 'admin/user/login',
		'http://example.net/login' => 'site/login'
	]

Path patterns may contain regular expressions and names for them, for example:

	[
		...,
		'post/<id:\d+>' => 'post/view'
	]

When the `view` action will be called, its $id argument will be set to the string matched by the `\d+` expression.

Named parameters may be used in the routes, for example:

	[
		...,
		'<controller:(post|tag)s>/<page:\d+>' => '<controller>/index'
	]

In this form, all named parameters must be present in the URL for the rule to apply, and vice versa, all parameters must be specified when creating a URL. To make some of the parameters optional, all the forms may be specified explicitly. Another way is to replace the pattern=>route form of the rule with the longer form:

	[
		...,
		[
			'pattern' => '<controller:(post|tag)s/<page:\d+>',
			'route' => '<controller>/index',
			'defaults' => [
				'page' => 1
			]
		]
	]

The `enableStrictParsing` option tells to throw yii\web\NotFoundHttpException if none of the explicit rules match. If this option is disabled and none of the rules match, the pathinfo will be taken as the route.


### Suffixes






## Creating URLs

Url::to(['post/index'])
Url::to(['post/view', 'id' => 42])






## Views

Views are PHP templates. The templates are called inside a context, so the `$this` variable refers to an instance of a view class. There also may be other variables defined by the caller before calling the view template. In the example below the `message` variable was defined somewhere before:

	<?php
	use yii\helpers\Html;
	$this->title = 'Login';
	?>
	<p><?= Html::encode($message) ?></p>

Views are stored in @app/views/<controllerID> by default. For example, if the controller class is `PostController`, the directory would be `@app/views/post`; if it is `PostCommentController`, the directory would be `@app/views/post-comment`.

Views can include other views using the `render` function:

	<?php
	echo $this->render('subviewname');
	?>

The subview is searched in the same directory as the calling view.

The view may be accessed globally using as `\Yii::$app->view`.


### Views search

When calling `render`, its argument may specify the view in several ways.

A simple name, like 'index', means 'index.php' file in the current view directory.

A path starting with one slash is relative to the views directory of current module. This means '@app/modules/<modname>/views', if <modname> is current module, or '@app/views' if there is no module.

A path starting with two slashes is relative to the root views directory. For example, '//site/about' means '@app/views/site/about.php'.


### Layout

Layout is a host view that will contain the view rendered by the controller. The view's content in the layout will be in the $content variable, so a layout might look like:

	<!DOCTYPE html>
	<html>
	<body>
	<?= $content ?>
	</body>
	</html>

Layouts are stored in @app/views/layouts or in @mod/views/layouts.

The default layout is @app/views/layouts/main.php. A controller may specify another layout in its $layout field:

	class PostController extends \yii\web\Controller {
		public $layout = 'post';
		...
	}


### Layout hooks

There are predefined hooks which may be relied on by other components. To honor this agreement, the layout has to contain the following calls:

* beginPage() before the layout code
* head() before the closing '</head>' tag
* beginBody() after the opening '<body>' tag
* endBody() before the closing '</body>' tag
* endPage() after the layout code


### Sharing data

View has the $params field that is shared by views and the layout. So, to pass some data to the layout, in the view:

	$this->params['foo'] = 'foo';

and in the layout:

	<?= $this->params['foo'] ?>


### Reusing views

A view may be included in another view using the `render` function:

	<div>
		<?= $this->render('_template.php', $vars) ?>
	</div>


## Widgets

Widget views are stored in <widget-path>/views.

For outputting views there is the `render` function, same as the one in controllers. There is also the `renderFile` function.


## Controllers

Controllers are instances of yii\base\Controller.

Controllers define 'actions'. An action is implemented as a controller's method with name starting with 'action'. For example, an action 'index' will be implemented by the method `actionIndex`.

	class PostController exters \yii\base\Controller
	{
		function actionIndex()
		{
			...
		}
	}

The default action for a controller is the 'index' action. A controller may specify a different action name in its defaultAction field:

	public $defaultAction = 'home';

An action method typically ends with rendering a content, a redirect, or an HTTP error.

To render content the `render($name, $vars)` method is used. It renders view $name with variables $vars, applying the active layout to the result.

	function actionView($id)
	{
		$post = Post::findOne($id);
		if(!$post) {
			throw new NotFoundHttpException;
		}
		return $this->render('view', ['post' => $post]);
	}

A redirect typically happens after some submitted data was processed.

	function actionSave()
	{
		<process data>
		return $this->redirect(['view', 'id' => $id]);
	}

The `redirect` method takes the same argument as the `Url::to` method to create the URL of the target page. The argument may also be a literal string with the URL.


## Filters

Filters are run before controllers and after controllers. A pre-filter may prevent the action from executing.

Filters are yii\base\ActionFilter instances.

* `beforeAction($action)` is run before the action. If it returns `false`, the action won't be executed.

* `afterAction($action, $result)` is run after the action.

When overriding these methods, pass the calls to the parent.

Filters are assigned in a controller class by overriding the `behaviors` function. The function returns an array of behaviour descriptions.

	function behaviors()
	{
		return [
			[
				'class' => 'yii\filters\HttpCache'
			]
		];
	}

By default the filters will be applied to all actions of the controller. A rule may list actions explicitly under the `only` key, or exclude actions by listing them under the `except` key.



## Models

Models extend from yii\base\Model. The Model object provides attributes, attribute labels and validation rules.


### Attributes

Attribute values contain the actual data. They can be accessed using both
object and array notation:

	$model->attrname
	$model['attrname']

Attributes are also traversable:

	foreach ($model as $attrname => $attrval) {...}

By default attributes of a model are non-static public fields, so the
model in the example below has two attributes:

	class ContactInfo extends \yii\base\Model {
		public $name;
		public $email;
	}


### Labels

Every attribute has a label for display, for example, in a form. To get a label, there is the function `getAttributeLabel`:

	echo $model->getAttributeLabel('attrname')

By default labels are generated from attribute names with the `generateAttributeLabel` function. For example, attribute 'firstName' will receive label 'First Name'. Labels can be given explicitly by overriding the `attributeLabels` function:

	class ContactInfo extends \yii\base\Model {
		public $name;
		public $email;

		function attributeLabel() {
			return [
				'name' => 'Name',
				'email' => 'E-mail'
			];
		}
	}


### Validation

The `validate` function checks the data and returns `true` if all validation rules are satisfied. If there are validation errors, they will be assigned to the `errors` attribute.

	if(!$model->validate) {
		var_dump($model->errors);
		exit;
	}

Validation rules can be defined by overriding the `rules` function:

	function rules() {
		return [
			// name and email are required
			[['name', 'email'], 'required],

			// email is an email
			['email', 'email']
		];
	}


### Scenarios

To enable certain validation rules only under specific circumstances scenario names are used. To restrict a rule only to a specific scenario the 'on' key is used:

	function rules() {
		return [
			// all required when registering
			[['name', 'email', 'password'], 'required',
				'on' => self::SCENARIO_REGISTER],

			// when logging in, only name and password are required
			[['name', 'password'], 'required',
				'on' => self::SCENARIO_LOGIN]
		];
	}

To specify scenario to a model the `scenario` attribute must be set:

	$model = new User;
	$model->scenario = User::SCENARIO_LOGIN;


### Massive assignment

Assigning data from a POST request may be done quickly using the `attributes` property:

	$model = new ContactInfo;
	$model->attributes = \Yii::$app->request->post('ContactForm');

This kind of blind assignment creates a risk of an expoit where the POST data will modify the attributes not indended for the modification in current scenario. To limit the attributes that may be assigned this way, the 'safe' rule may be used:

	function rules() {
		return [
			...
			[['name', 'surname'], 'safe']
		];
	}

If the model uses scenarios, then only fields mentioned in rules for the current scenario are considered safe.


### Exporting data

To export model data to array the `toArray` function is used.

	$data = $model->toArray();

To affect output of the `toArray` function, the `fields` function has to be overridden:

	function fields() {
		return [
			// return 'id' attribute as is
			'id',

			// return 'email_address' attribute as 'email'
			'email' => 'email_address',

			// return the function value as 'name'
			'name' => function() {
				return $this->name.' '.$this->surname;
			}
		];
	}

To omit fields from the output `unset` may be used:

	function fields() {
		$fields = parent::fields();
		unset($fields['pwhash']);
		return $fields;
	}


## ActiveRecord

ActiveRecord is an instance of yii\base\Model.



## POST data

	$data = \Yii::$app->request->post('ContactForm', []);


## Assets

Assets are CSS and JS files. Widgets and views may add assets to the generated page automatically. Assets are combined into 'bundles', which are instances of `\yii\web\AssetBundle`. A bundle specifies asset files.

If the referenced CSS and JS files are stored outside of the web-accessible tree, they are called "source assets". A bundle referencing such files must have $sourcePath field set to their directory. Source assets are automatically copied to a web-accessible directory when needed.

A bundle may specify other bundles on which it depends in the $depends field. A bundle is specified by its full class name, without the trailing backslash.

An example of a bundle is the `AppAsset` bundle from the basic template:

	class AppAsset extends \yii\web\AssetBundle {
		public $basePath = '@webroot';
		public $paseUrl = '@web';
		public $css = ['css/site.css'];
		public $js = [];
		public $depends = [
			'yii\web\YiiAsset',
			'yii\bootstrap\BootstrapAsset'
		];
	}

### Options

The $cssOptions and $jsOptions fields may contain options maps for the corresponding files. The CSS options may be:

* `'condition' => 'lte IE9'` - surround with IE conditional comments
* `'noscript' => true` - put inside a <noscript> block


The JS options might be:

* `'position' => \yii\web\View::POS_HEAD` - put the scripts in the head


### Including

To include an asset bundle, the view has to be registered with it using the register static function. For example, a view may have the following call:

	\app\assets\AppAsset::register($this);

where $this is a View instance.


## Creating forms

If there is a 'model' class for which the form data is collected, then `yii\widgets\ActiveForm` is the preferred way to render a form.

	$form = ActiveForm::begin([
		'id' => 'login-form',
		'options' => ['class' => 'form-horizontal']
	]);

	echo $form->field($model, 'username')->hint('John Doe')->label('Name'),
		$form->field($model, 'password')->passwordInput(),
		Html::submitButton('Login');

	ActiveForm::end();


## Logging users in

The application needs to implement `\yii\web\IdentityInterface`:

* `getId()` - returns identifier of the user
* `getAuthKey()` - returns key used in the login cookie
* `validateAuthKey($key)` - returns true if the given key is valid for the user
* `findIdentity($id)` - returns an instance with the given identifier
* `findIdentityByAccessToken($token, $type = null)` - returns an instance with the given access token

The name of the new class has then to be assigned to the `identityClass` property in the `user` part of the config:

	...
	'user' => ['identityClass' => 'app\models\User'],
	...

Also, the 'enableAutoLogin' key has to be set to true to allow using cookies.

To set user identity:

	\Yii::$app->user->login($identity, $duration);

To check identity:

	\Yii::$app->user->identity
	\Yii::$app->user->id
	\Yii::$app->user->isGuest

To reset:

	\Yii::$app->user->logout();


### AccessControl filter

yii\filters\AccessControl. Checks a list of access rules to determine if the user is allowed to make the requested action.

The filter is added to the controller as a "mixin":

	function behaviors() {
		return [
			[
				'class' => AccessControl::className(),
				'rules' => [...]
			]
		];
	}

The rules are scanned in the order they were defined, until the relevant rule is found. If no rules matched, the access is defined.

A rule is specified as array with keys:

* `actions` - list of actions the rule applies to;
* `allow` - true to allow, false to deny;
* `roles` - '?' for guests, '@' for authenticated users.
* `ips` - list of IP addresses.
* `verbs` - list of request methods (GET, POST).
* `matchCallback` - callback to determine if the rule is relevant, in the form f($rule, $action);
* `denyCallback` - callback to be called if the user is denied.

When a guest user is denied, the yii\web\User::loginRequired function is called, which is supposed to redirect to a login page. For an authorized user, the yii\web\ForbiddenHttpException is thrown. This behaviour may be overridden by providing a callback to the `denyCallback` property of the filter.


## Behaviours

Behaviours, or mixins are intended to side-extend functionality of an arbitrary component of the framework.

Behaviours are instances of yii\base\Behavior.

	class beh extends \yii\base\Behavior
	{
		...
	}

To add a mixin to a component, override its `behaviors` method:

	function behaviors() {
		return [
			'behaviourClassName'
		];
	}


## Database

Queries are parameterized:

	$cmd = \Yii::$app->db->createCommand(
		'SELECT * FROM user WHERE id = :id'
	);
	$user = $cmd->bindValue(':id', 123, PDO::PARAM_INT)->queryOne();

Also bindValues:

	$cmd->bindValues([':id' => 123]);

Also a shortcut:

	->db->createCommand('SELECT ...', [':id' => 123])->queryOne();


## Returning non-HTML respose

Normally an action returns the result of the `render` function which renders a template. But an action may return any data, string or array, and have it rendered appropriately. The format has to be specified in $app->response->format field:

	function actionSome()
	{
		$data = ...
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		return $data;
	}

Any other junk can be returned from an action as long as it can be treated as a string or an array.


## Language

The language is set in the `language` field:

	\Yii::$app->language = 'ru_RU';

The same may be written in the config:

	return [
		...
		'language' => 'ru_RU'
	]
