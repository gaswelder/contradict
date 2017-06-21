First date should be the date when the recording was finished.

Other dates might come from release history:
	date	label_id



# Editors

## Tracklist editor

Format for a track is:

	[id] <band id> name - length [comment]
	<band id> name - length [comment]

Example:

	[314] Track name one - 3:15 [instrumental]
	[315] Track name two - 9:18
	[316] Whatever - 4:16
	<44> Something else - 5:20


If all albums are only original, why bother with track identifiers?
-- to allow editing


## Credits

General format is:

	a) stagename (real name) - roles [tracks]
	b) name - roles [tracks]

Credits format applies to lineups, engineers and producers.

Example:

	Christopher (Petr Krystof) - guitar, lead vocal
	Martin Mikulec - guitar
	Bruno (Bronislav Kovarik) - bass, vocals
	Kopec (Petr Kopecek) - drums
	+ Petr Ackermann - keyboards [1, 5]

In the lineup case "+" sign before the name means that the person is
marked as a "guest". Guests are not counted when building lineup lists
on the band page.


parse into (name, stagename, role, track_id) rows.
replace references with new ones
- this will probably create new person records, therefore:
clean unreferenced person records


## Engineers and producers

Tom Lippold - recoding, mixing [1,2]


## Studios

Many albums are done in stages in different studios.
For example, the raw tracks may be recorded in one studio,
then they may be mixed in another studio,
and finally mastered in a third studio.

I.E.V. Studio - recording [1,2]


Tchelo Martins @ Da Tribo Studio - recording
@ Fabrica Producoes - mixing
Gene Palubick, Erik Rutan @ Dimensional Sound Studio - mastering

person_id	studio_id	work
tm			tribo		recording
NULL		fabrica		mixing
gene		dimens		mastering
erik		dimens		mastering




## Song credits

Song credits are for lyrics and music.

Lyrics
1, 4 - Hellid
2, 3, 7, 8 - Andersson
5, 6, 9 - Hakansson

Music
1, 7, 8, 9 - Andersson
2 - Andersson, Cederlund
3, 4 - Rosenberg, Andersson, Cederlund
5, 6 - Andersson, Cederlund


Hellid - lyrics [1, 4]
Andersson - lyrics [2, 3, 7, 8]
Hakansson - lyrics [5, 6, 9]
Andersson - music [1, 7, 8, 9, 2, 3, 4, 5, 6]
Cederlund - music [2, 3, 4, 5, 6]
Rosenberg - music [3, 4]



## Release credits
