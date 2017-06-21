<?= template('top') ?>

<h1>Error types in Go</h1>

<p>The errors design is uninteresting and pragmatic. It seems, they tried
to do two things: (1) make up for the decades of mess made of error
handling in Unix and C, and (2) compel the lazy programmers to check the
errors.</p>

<p>The first problem is well known: some early C APIs use the errno global
variable to return errors; others return negative values because zero
is a valid response; others return zero because it's convenient for use
in conditions. There are probably some functions that return positive
values for error too. As a result of this variety, some mistakes are
constantly made.</p>

<p>So the Go way of returning errors is uniform and doesn't cause
confusion. What really is interesting, though, is whether the time
spent on writing "if err != nil" every time or on inventing tricks to
have less points where errors are returned, is less than the time spent
on dealing with the confusion of old ways of error handling.</p>

<p>The second problem is also well known. Those lazy programmers. They
don't even bother to check the errors in many cases, not to mention
looking up the manual, so we have:</p>

<pre><code>foo = getfoo();
// It didn't fail when I programmed it!
</code></pre>

<p>How do we solve that problem? First, let's return errors from
everywhere as a second (or, more generally, the last) argument. Then
have the compiler demand that all returned values are assigned:</p>

<pre><code>&gt; Compiler error: you screwed up return values at line 49.

foo, err := getfoo();
// OK, happy now?
</code></pre>

<p>(By the way, I wonder if having to return an error along with the
function's primary value was the sole reason of implementing the
multiple-value return.)</p>

<p>Second, make the programmer actually use that variable:</p>

<pre><code>&gt; Compiler error: use that variable at line 49, you punk!

foo, err := getfoo();
if err != nil {
    // Take it easy, pops.
    panic("They made me do it");
}
</code></pre>

<p>Does it work? Actually, it does.</p>

<p>But then we saw some preaching and mantras. The one about errors is:
"errors are values". What Rob meant by that is probably that errors
should be returned from functions, which they are now indeed.</p>

<p>But errors are not values. You don't take square root of a "disk read
error"; you don't put "division by zero" error into an array, you don't
take hash of a "permission denied" error. This is all nonsence. If you
do have some errors used as normal values, then they are really normal
values, not errors, you just haven't realized that yet.</p>

<p>Errors are just that - errors, which by nature are conditions. Based on
conditions you decide what to do next: retry the last action, take a
different action, or abort the entire operation. That's what errors are
for. In that sense, the errno approach is actually closer to the truth,
albeit less convenient as it is. And if someone thought about errors
as errors, not values, maybe we would have a more convenient interface.</p>

<p>By the way, do thousands of programmers really appreciate now, after
typing NULL for decades, having to reprogram their fingers to type nil
instead?</p>


<?= template('bottom') ?>
