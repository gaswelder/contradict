# Hello, client

Suppose you run a remote service where customers call by phone to get some information (say, the current temperature in your office). How do you do that? Several ways have been developed over the history of operating systems and programming.


## Buzz off!

First and the most straightforward approach is to get a desk with a
phone, and get a girl, say Jane, to answer it. So whenever a customer
calls, Jane takes the phone and says:

	"Hello; temp is 70."

And then to keep the line free for the next customer she says in Dirty Harry's voice: "Buzz off!" - and hangs up.

This loosely translates into the following pseudo-code:

	jane(phone) {
		loop {
			wait for a call
			line = accept()
			send(line, "70")
			close(line)
		}
	}

The problem with this approach, as already mentioned, is that the line is busy while another customer might try to call in. And if the customers won't buzz off quickly but want to keep chatting with Jane, that will become a problem.


## Multiplexing

The next solution is to give Jane a nicer phone, with several
lines (say, 32) and a notepad. Now, whenever a call comes in, a button
lights up. Jane presses the button, says hello, and starts writing
information about the customer on the notepad. She is also able to say
"wait" and switch to another customer (going to the appropriate page in
the notebook) and thus distribute her time between the customers.

This was called "multiplexing" because several Bobs are multiplexed
over one Jane. In code that might look somewhat like:

	jane(line) {
		loop {
			wait for some activity
			what is the activity? {
				incoming call:
					call = accept(line)
					put call to the notebook

				someone speaks:
					call = select(page from notebook)
					chunk = listen to what is said
					reply to the chunk
			}
		}
	}

This looks complicated, but it allowed Jane to talk to several Bobs
almost simultaneously. This works especially well if the Bobs
are slow and keep silent for significant amounts of time, so
Jane can manage without much effort.

One problem is, if someone asks Jane a question more complex than usual, like what the temperature was on 15-th April at 3 AM, she will have to run to the archives to get the data. While she will be away, all the Bobs online will have to wait.

Besides that, and the complicated implementation, there are no problems with this approach. But historically the `select` and the slightly improved `poll` calls are inefficient, probably because the select wasn't intended for big numbers of connections to begin with.


## Jane++

So the next approach to solve this problem is to have, say, Mary at the
reception, and a team of virtual Janes somewhere. Now, whenever a
customer calls, Mary takes the call, says "Hello", puts her hand over
the mic, and yells: "Jaaane!!!". Then a few guys run in, carrying a
desk with a phone, and put it in place, and then Jane comes in from
somewhere, sits down at the new desk, and takes the call.

Mary by that time has already switched the customer to the new line and 
is waiting for the next incoming call. When another call comes in, all 
repeats: same guys run in with yet another desk and phone, and yet 
another Jane sits down to chat. Whenever one of Janes hangs up, she 
gets up and goes out, and the guys move the desk out.

This is the multiprocessing approach which translates into something
like this.

	mary(main_line) {
		loop {
			wait for a call
			line = accept()
			jane = get_jane()
			start in parallel jane(line)
		}
	}

	jane(phone) {
		talk to customer
		hangup(phone)
		exit
	}

There are two famous mechanisms that allow this: "fork" and "threads".
The difference is purely technical and doesn't affect the logic for
Mary or Jane.

The problem with this approach is that in reality the number of Janes
is limited. In which case there are two outcomes: either Mary stops
accepting new calls when all Janes are busy, or Janes start running
from desk to desk trying to talk with several customers at once.

As a typical number of Janes at the time threading was invented was
probably around "one", and now is typically four to sixteen, the "stop
accepting" wasn't really used. So it was obvious that Janes should run from desk to desk.

When it becomes time for a Jane to move to the next desk, she writes 
all the important information about the customer on a piece of paper 
and leaves it for whichever Jane happens to come there later. That 
later Jane can read the notes and continue the conversation from where 
it was stopped. This Jane, of course, will similarly get a piece of 
paper at the desk she is going to.

This running around and reading and writing notes is called "context 
switching" at the CPU level and "state managing" at the application 
level. The essence is that we constantly write and read the state, and 
the problem with it is that if the number of customers becomes too big, 
Janes will spend most of their time running around, reading and writing 
notes and will have almost no time to actually speak to the customers.

That's why the "one thread per request" model is frowned upon: when you
get too much requests, everything collapses.



Next level: predicting Gophers?



Of course, there are lots of technical details and optimization. Like,
maybe, we can have eight Janes and up to 16 customers maximum, after
which Mary will stop receiving calls. Or maybe the number of customers
will be regulated according to how Janes feel: if the customers are not
too chatty, we can have more or them, or less if they are heavy
talkers. But this is all below our conceptual view of the problem, and
should be considered by designers of operating systems (it wasn't, it
seems, until the Go language was released which has threads so
lightweight and practical that many people worry about calling them
"real threads").


## Events!

So, what were operating system designers busy with, if not making threads practical? We come to the snobby "event-driven" paradigm.

We are back again at the office with Jane. She sits at the desk, with a
phone and a conveyor. The conveyor brings to Jane small post-it notes,
called "events". A note might say something like: "Bob on line 19 wants
to say something", or "Incoming call on line 10". Jane reads notes and
reacts to them by pressing "19" or "10" on her phone, talking to Bobs
out there, writing notes in her notebook, one page for each Bob...
Wait, wait!.. Is that... Is that the multiplexing approach again?

Yes it is! This is the glorified multiplexing approach, now pompously
called "event-driven programming". We won't even bother to come up with
a pseudocode because it is the same in principle to the one for the
multiplexing case.

What is the difference then? The difference is purely technical. The `select` call worked badly when the number of calls was big, but the new conveyor just keeps working steadily no matter how many Bobs are online. Jave doesn't see what goes on at the other side of the conveyor, because it's in the other room called "kernel space", but the notes keep coming without problems.

So that's what operating system designers were busy with! They were
taking us back to 1980-s, when Jane has to have a notepad and keep
track of all the Bobs herself. Only this time the "kernel space"
doesn't melt under a flow of Bobs eager to speak to Jane. This famous
"C10K" problem again: service 10 thousand Bobs at a time no matter how
Jane feels about that. We have it now. Congratulations.
