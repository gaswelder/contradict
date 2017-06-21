# Programming puzzles

If you're a programmer, there is a kind of puzzle
available for you: someone else's code. But not any code. Code written by
capable people is already solved: it's like looking at solitaire decks
that have been arranged. There's nothing you can
move or change, it's all perfect.

There is also code written according to some strict discipline. Coding
disciplines were presumably created to help turn millions of kids into
great programmers (or, rather, program them to be ones). For example,
there are often rules telling how variables must be named (like, "no
less than 5 characters, exactly two words in camel case"), how many
language elements there must be in each file (for example, "exactly one
class in a file") and so on. Code written according to such rules is
also of no interest for solvers: it is plain, mechanistic, redundant,
and there is nothing you can do about it except feel depressed.

But what other code is there? There is code written by idiots. This is
a great material for puzzles. You could find an old program source and
try to rewrite it, if you feel like it. This kind of entertainment pays
off when you start thinking about why some particular part of the code
you are solving is incorrect and, more interesting, why it still works.
Take, for example, this C function:

	void bail (str, p1, p2, p3, p4, p5, p6)
		char * str;
		char * p1, * p2, * p3, * p4, * p5, * p6;
	{
		fprintf (stderr, str, p1, p2, p3, p4, p5, p6);
		close (netfd);
		sleep (1);
		exit (1);
	}

This is an edited code from somewhere I won't dare releal. (If you still
manage to find the original, take a look at the comments there too:
it's a good material for training your tolerance of offense and
bitching.)

What can we learn from this piece of puzzle? Well, see how there
are 6 explicit, yet optional arguments? No va_list needed. And the call
to fprintf works too. How? Maybe the compiler
just arranges place for all stated arguments in the call frame. But
why doesn't it throw an error about missing arguments? Because it's the old-style C function declaration?

Another interesting thing: is it safe to call close on some value
'netfd' floating in the sky? Makes you think: what will happen if you
close a descriptor twice? Will it set the `errno` value and screw up
the execution of some other, unrelated function? Or what if you try to
close a nonexistent descriptor? You read the spec and get to know more:
an outcome you are unlikely to have from filling a Sudoku field with
digits.

And yet another one mystery: why would you need to wait exactly 1
second before exiting? What can go on in parallel in a program with
only one thread? What we might have to wait for? Maybe, the operating
system might do something with the program, like asynchronous
input/output, or some interrupts, maybe? What if you don't wait and
just exit? Will the operating system crash because we didn't wait for
it to bring us a network packet? And how you decide how many seconds
you need to wait?

See how the puzzle makes you wonder?
