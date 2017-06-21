# Kinds of variables

When writing a moderately complicated program, like a browser widget in
Javascript, one may get overwhelmed by all the state, parameters and
events that are involved and start making erroneous decisions. I, for
example, tended to have too much explicit state which lead to one of
the greatest programming problems, the incorrect caching.

Then I tried to take a more abstract view on this, without features of
Javascript or any other language interfering with thinking, which led
me to the following. There are three kinds of variables: registers,
parameters and caches.


## Registers

Registers are variables used to hold intermediate values in some
computation. For example, the sum variable in the program calculating
a sum of elements of an array would be a register:

	reg sum = 0; // register
	for each element e {
		sum += e;
	}

The example above is a pseudocode, which helps, as mentioned, to avoid
interference from features of a familiar language.


## Parameters

Parameters represent program state. For example, a position variable is
a parameter which might affect how the model is drawn on screen. By
changing it one could manipulate the output in a straightforward
manner. In a multiprocessing implementation parameters may need to be
protected with a mutex. For example, one thread might redraw the model
on screen while another thread is changing the parameters continuosly,
thus resulting in an animation.

	param position = 12;

	/*
	 * One thread manipulates the state variable
	 * in some manner
	 */
	thread 1 {
		loop {
			position += 1;
			sleep(0.001);
		}
	}

	/*
	 * Another thread just draws the model in its current
	 * state and isn't bothered with how that state changes,
	 * if changes at all.
	 */
	thread 2 {
		loop at 30 FPS {
			render(position);
		}
	}

Note how beautifully this "separates the concerns" of the manipulating
the model and the rendering. By the way, this separation, which is presumably so obvious that no one bothered to publicize it intensely, has much more right to be called "MVC" than the tons of web frameworks appeared out there overnight.


## Cache

Cache variables store values needed only by the program and not the
model. Changing it externally would result in the program producing
incorrect results. For example, a maximum character height might be a
cache variable and changing it might result in wrong rendering of a
text. Values of cache variables can always be calculated from the given
data and current values of the parameters. The cache variables can be
omitted completely, and the calculation can be used instead every time.
But these calculations may be expensive, so caching is used.

In out pseudocode we might define the cache variable together with the
code the computation of which it holds.

	cache maxWidth {
		reg width = 0;
		for each element {
			if(element width > width) {
				width = element width;
			}
		}
		return width;
	}

	refresh maxWidth;

The definition and the refresh operation both make sense, but there is
no need to bother with such trivia in a real language. Once one knows
which variables are cache, they would more easily make decisions about
their use. It would be more evident that explicit calls to refresh the
cache would have to be placed in specific places of program. And
sometimes it might also turn out better to just compute some value
every time and keep the code clean and easy to understand instead.
