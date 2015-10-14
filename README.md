Readme - Simant V. 113

About

Sim ant is a small script I made on the challenge that Finite State Machine based
AI was dificult if not impossible in PHP. Not only did it prove possible, but also
quite modular.

Licence

This script is meant for entertainment purposes only, and not to be used in any
commercial product.(but why would you?)

If you are going to use this script or modifications of it, you must agree:
	A.)Contain a link to this Bitbucket Rep in your files:
		-https://github.com/Nymall/SimAnt/
	B.)Maintain Credit of the original Script to Nicholas Fagerlid.
	C.)Include This readme file with the source of the project.
	
Useage

This script is based off of several different iterators. If you would like to add
a new nest, it as simple as adding this to generatenew() from the WORLD class:

	$this->nest[ COLOR ] = new NEST( COLOR , XCOORD, YCOORD, $this, 0, true);
	
	Where
		COLOR = The unique color of the nest.
		XCOORD = The X Coordinate.
		YCOORD = The Y Coordinate.

Removing nests is litterally as simple as deleting the entries from this 
location.

Setup

Most of this program is ready to go out of the package, but there is two things
you need to do. First, you need to make a "saves" directory at the same level as 
the index.php file. That's pretty straight forward.

The second part requires some manipulation. You will need to set a job to clear
out the saves dirrectory periodically, as the script is unable to do it itself.
Setting up a cron job is suprizingly trivial, and your host should be able to
help you through some of the more technical aspects.

Changelog

Please see Changelog.inc or straight off of index.php.
