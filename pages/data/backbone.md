# Backbone

Installation:

	bower install --save backbone


## View

Assume there is an empty container on the page with id=someid. The following script will put 'Hello world' there:

	var View = Backbone.View.extend({
		el: '#someid',
		initialize: function() {
			this.$el.html('Hello world');
		}
	});

	var view = new View();

`el` is a selector of an existing container. `el` may be given when extending a view or when creating an instance.

If `el` is unknown at the moment of creation, an empty DIV will be created and assigned. 

`$el` is set to the corresponding jQuery object automatically.

Creating instances of View will create new document elements.

Or, creating instances with `el` defined will link the element:

	var view = new View({el: $('#someid')});


Each view is bound to a particular document element and often to a model or a models collection.

When a model is updated it fires an event. The view tied to that model listens to relevant events and updates itself. This can go the other way too: an event on the document element may trigger the view's listener which will update the model.







## Model

A "model" is defined like this:

	var Product = Backbone.Model.extend({
		...
	});

The result can be extended also:

	var Product2 = Product.extend({
		...
	});

Models, like views, also have the `initialize` attribute.

** [?] In what order different `initialize` instances are called for objects extended multiple times?

Objects are then created as usual:

	var item = new Product();

Instances of models provide `get` and `set` functions:

	var t = new Task({title: 'task1'});
	t.get('title');
	t.set('complete', true);
	t.unset('title');

These functions should be used instead of direct assignments or reads because they do some namespacing when necessary, and dispatch relevant events.

The `set` function triggers the `change` event on the model and a property-specific event like `change:title`. Every model has `on`, `off` and `trigger` functions.

The `set` has the `silent` argument which tells not to fire the events:

	t.set({title: 'task1a'}, {silent: true});


Every instance of the Model type will have the `save` function:

	item.save();

Backbone expects that the models will have a storage of some kind. The default one is a web resource that takes GET, POST, UPDATE and DELETE requests. There is also a localStorage plugin.

The `extend` function accepts the `defaults` parameter:

	var Task = Backbone.Model.extend({
		defaults: {
			title: '',
			complete: false
		}
	});

Every new object on this type will have the values specified in the `defaults`.

Custom fields can be used to describe other properties of the model objects:

	var Task = Backbone.Model.extend({
		...,
		toggle: function() {
			this.save({complete: !this.get('complete')});
		}
	});
	
Now calling `task.toggle()` we will toggle and save the `complete` flag.

If the `validate` function is defined, it will be called on every save. It will also we called on every `set` call with argument `validate: true`. The `validate` function will return a string in case of error:

	item.validate = function(attrs) {
		if(!attrs.name) {
			return 'Missing name';
		}
	};




## Collections

Collections are containers for model instances.

//? They provide means to load and save the data to a database.

	var Tasks = Backbone.Collection.extend({
		model: Task,
		localStorage: new Store("someid")
	});

The `model` property points to the constructor of child elements.

	var list = new Tasks();
	list.create({title: 'task2'});
	list.add(new Task({title: 'task3'}));

	var titles = list.pluck('title'); // 'task2', 'task3'
	var finished = list.where({complete: true})




## Routing

Router uses the History API and URL segment part for navigation.

Routers are extended from the `Router` object. Routers match URLs to functions.

	var Router = Backbone.Router.extend({
		routes: {
			'*filter': 'setFilter'
		},
		setFilter: function(params) {
			...
		}
	});


