# How to use a library

How is a library born? There are two scenarios. First is "let's make a library": someone decides to write a library and writes it trying to guess which functions will be needed by future users. This approach is unlikely to produce practical results exactly because of the method: the authors are guessing or discovering the user's needs as they write. The BSD sockets API was probably done this way, and we know how it turned out.

Second scenario is growing out of another project. As some project evolves, it accumulates lots of auxilliary functions that help write the main program. If those functions are similar in nature and are not too specialized, they may be gathered in one place and promoted to a library. This evolutionary approach is more likely to produce something useful as it is already useful right from its birth.

Let's consider the second scenario, where we have a project P under which a library L gradually evolves. At some point, the source tree of P undergoes a big change where some functions are moved from several places into one new file, which becomes the source for L. The project P now oficially depends on the library L. The question is: should L be excluded from P's source tree now that it's a separate entity? Some might argue that it shouldn't because it always was there in the first place, and why should we delete it now that it has its own name? But other projects which will use L will see it only as an external library...

If some other project O uses some functions from multiple libraries L[i], and the libraries are missing from the source, the project is incomplete. The user getting a copy of O will have to hunt for every library L[i], probably even some specific versions of them. And if those libraries happen to also "depend" on some other libraries, or even on specific versions of each other, the "end user" is in for a real treat. This is why all libraries have to be really included in the source and verified by the developer. Otherwise every user will have to do this work on their own.

It might also be practical because compilers could use libraries as libraries, getting only the needed functions from them rather than appending the whole blobs to the program.

It might also be liberating because program developers won't feel they have to write the few silly functions themselves rather than make users search for a small required library: just put it in and use it. And library writers won't have the psychological pressure to make their libraries more "substantial" (which in practice turn out to mean "bloated") to justify them "worthy by size" of being an external dependency.

Everyone will be happy... except shared library proponents. There are two reasons shared libraries are used: saving memory and security updates.

Probably the first and the most justified shared library is the C library. If operating systems didn't provide it transparently, every program, as well as its memory footprint, would grow by few kilobytes (oh, my) {kilobytes, not megabytes, because every programs uses only a tiny bit of C library} since it would have to carry some C functions in it. Not such a big waste nowadays. But the C library has another reason to be shared: updating the operating system could break the programs since many C functions are specific to some kernel version. But all non-system libraries don't have that peculiarity.

Another thing is updates. Imagine a library as common as the C library, but not system-specific. Everyone uses it, and it's a shared library. One day a vulnerability is found: a buffer overflows somewhere, and kids exploit it to make programs print insults and buzz the floppy drive. Tech journalists spread mass panic about killer floppies, police launch Git or SVN investigations of who didn't put `-1` on line 730 of `somecrap.c`, governments prepare to launch nuclear strikes as few "security experts" give exclusive interviews... After several hours, a fix is rolled out, a thousand of didactic articles is written telling that you shouldn't let buffers be overrun, and only one file has to be updated (per system) to cure all the programs, thanks to the library being shared.

Well, stopping nuclear warfare with just one file replacement (per system) is certainly better than having to recompile every program. But are there such non-system libraries that are at least 10% as ubiquitous as the C library?

And this cuts both ways: an update could as well break all the depended programs too.
