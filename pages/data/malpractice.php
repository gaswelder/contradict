<?= template('top') ?>

<h1>Code malpractice</h1>

<h2>C++ superiority complex</h2>

<p>Consider this piece of code:</p>

<pre><code>#ifdef __cplusplus
extern "C" {
#endif
</code></pre>

<p>As everyone knows, <code>extern "C"</code> instructs a C++ compiler not to "mangle"
symbol names when compiling so that the resulting object can be linked
with other C code. This is reasonable and necessary if you write a
library in C++.</p>

<p>But what about those ifdefs? They check if the compiler is a C++, which
means the library is actually written in C. Why would someone compile C
code with a C++ compiler?</p>

<p>Even though C++ started as a C extension, that was before many of us
were born. C++ has evolved so much that it doesn't look like itself
twenty or even ten years ago. But for decades there has been a
sentiment (the Stroustroup conjecture) that C++ is the new C, and C itself should be deprecated.
Some therefore concluded that all C code will some day be compiled with
C++ compilers and started putting those checks in.</p>

<p>But it didn't happen. C++ didn't phase C out in 1990's, and it
certainly won't happen now. C stays, albeit mummified, but still
coherent and efficient while C++ is going off the rails repeating the
fate of PL1, so the original sentiment is not even valid anymore as the
two languages became incompatible.</p>


<h2>C++/C shizophrenia</h2>

<p>Another problem with C++/C rivalry is that some people can't decide
which language they prefer. Or often some people convince themselves
that they know C++ when they actually don't: they write in C with some
C++ bits sprinkled around. This sometimes leads to things like this:</p>

<pre><code>type_t *foo = new type_t();
...
free(foo);
</code></pre>


<h2>Chameleon code</h2>

<p>GNU is a great project. But after some time in its history its agenda
has become not writing good free code, but writing free code that could
be distributed and adopted as widely as possible. That means, it's
acceptable for them to use tricks to allow compiling a program on a few
more systems, even at cost of quality of that program. For example, in their coding stardards there is this piece that they recommend:</p>

<pre><code>/* Declare the prototype for a general external function.  */
#if defined (__STDC__) || defined (WINDOWSNT)
#define P_(proto) proto
#else
#define P_(proto) ()
#endif
</code></pre>

<p>Then they invented Autotools, which is an automated collection of
porting tricks. There are two big problems with them.</p>

<p>First, Autotools effectively generate code using detected system
parameters as input: it's a one-way mapping. In extreme cases, which
probably occur, every system produces its own unique version of an
Auto-enabled program. There are no integrity and correctness guarantees
about a generated program.</p>

<p>Second, people who use Autotools for non-GNU programs probably assume
it just makes their programs compile flawlessly on systems supported by
the tools. All one has to do is run ./configure &amp;&amp; make... But it's not
true. Instead of dealing with compiler error messages you have to deal
with the script error messages which may be way more obscure and
sometimes plain wrong.</p>

<p>There is also blind abuse of Autotools by people who probably don't
have a clue about what they are doing. Like in this example:</p>

<pre><code>#if HAVE_STDIO_H
#include &lt;stdio.h&gt;
#endif
...
printf(...);
</code></pre>

<p>What if we don't HAVE_STDIO_H? Will it still work? If not, then why
this #if magic? System headers sniffing should be (if at all) in
libraries that unify different features of different operating systems
in a single API. Instead, we have people checking whether they have
standard C headers for occult reasons.</p>

<p>Closely related thing about Autotools is feature macros. They features
were supposed to be orthogonal and optional, so one could disable all
of them and still have a valid program. But look what we have
sometimes:</p>

<pre><code>int generate_random_bytes(unsigned char *buf, size_t length)
{
#ifdef DEV_RANDOM
    ...
#elif defined(HAVE_EGD)
    ...
#elif defined(HAVE_TRUERAND)
    ...
#endif  /* DEV_RANDOM */
    return SUCCESS;
}
</code></pre>

<p>Now, if you don't "opt in" for any of those features, you get this
pseudo-random numbers generator:</p>

<pre><code>int generate_random_bytes(unsigned char *buf, size_t length)
{
    return SUCCESS;
}
</code></pre>

<p>This is one successful generator! Presumably, the
build script would catch such a situation, but that would be like
catching rain drops. There are too much possible combinations of
constants to catch them all. And while we are at this example, there's
another malpractice:</p>


<h2>Random numbers arrogance</h2>

<p>Generating "good" pseudorandom numbers is not easy. Even defining what
is "good" might lead to problems. So one probably should study a
lot before they publish an implementation of random number generator.
This is a mini-version of the same drama about cryptographical
algorithms.</p>

<p>But this is right, you shouldn't have people rely on your unverified
algorithms. Some people say that... and then they go and publish their
own version of 'good_random_bytes'.</p>


<h2>Portability arrogance</h2>

<p>How many libraries have you seen that define their own types for
everything? When Bob starts his bob-runtime library, he ends up coming
up with types like <code>bob_int</code>, <code>bob_int32</code>, and
even <code>bob_string</code> (which is actually confusing since it is
typically equal to "<code>char *</code>" and doesn't make any
difference).</p>

<p>It's unclear why people do it. It could be that when Bob started his
library 30 years ago, compilers were so brutal that didn't care to
provide <code>int32_t</code> and the likes. But still, why would Bob
need things like <code>bob_string</code> and <code>bob_pointer</code>
(which is <code>void *</code>)? One possible explanation that comes to
mind too often is arrogance: Bob knows better what a pointer should
look like.</p>


<h2>Memory management arrogance</h2>

<p>Remember Bob? Well, he was so arrogant he wrote his own memory
allocator!</p>

<p>There are two kinds of arguments for doing such a thing. First is
"performance". Some bobs really think that they can allocate memory
more efficiently than the operating system, by "driving" the OS
allocator according to some kind of heuristics. For example, Bob might
decide to allocate only stripes of 1 megabyte internally and then hand
out different areas of those stripes. Or there could be buckets for
"small" allocations, "average" ones and "large" ones. Or maybe
something else Bob might come up with.</p>

<p>Another reason is "safety". Some Bobs believe that managing memory in C
is so hard that something has to be done about that. Sadly, what they think has to be done is not learning better programming techniques, and not moving to a less "dangerous" language. But something like
"memory pools".</p>

<p>Memory pools could be seen as an optimization (since it is
practically the slices approach from the previous example), but in
practice it is an excuse for not calling 'free'. For example, if you
have a server processing requests, there might be a separate pool for
every request, and that pool could be simply destroyed after the
request has been processed. That might be a compelling argument, but
this leads to more serious leaks where instead of screwing up one
allocation you leak or corrupt an entire pool just by passing it
somewhere beyond the intended scope.</p>


<?= template('bottom') ?>
