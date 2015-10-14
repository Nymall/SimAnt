<html><head>
<script src='jquery-1.11.0.min.js'></script>
</head><body>
<script>
//CSVToArray by Ben Nadal
//http://www.bennadel.com/blog/1504-Ask-Ben-Parsing-CSV-Strings-With-Javascript-Exec-Regular-Expression-Command.htm
// This will parse a delimited string into an array of
// arrays. The default delimiter is the comma, but this
// can be overriden in the second argument.
function CSVToArray( strData, strDelimiter ){
// Check to see if the delimiter is defined. If not,
// then default to comma.
strDelimiter = (strDelimiter || ","); 
// Create a regular expression to parse the CSV values.
var objPattern = new RegExp(
(
// Delimiters.
"(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +
 
// Quoted fields.
"(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +
 
// Standard fields.
"([^\"\\" + strDelimiter + "\\r\\n]*))"
),
"gi"
);
 
 
// Create an array to hold our data. Give the array
// a default empty first row.
var arrData = [[]];
 
// Create an array to hold our individual pattern
// matching groups.
var arrMatches = null;
 
 
// Keep looping over the regular expression matches
// until we can no longer find a match.
while (arrMatches = objPattern.exec( strData )){
 
// Get the delimiter that was found.
var strMatchedDelimiter = arrMatches[ 1 ];
 
// Check to see if the given delimiter has a length
// (is not the start of string) and if it matches
// field delimiter. If id does not, then we know
// that this delimiter is a row delimiter.
if (
strMatchedDelimiter.length &&
(strMatchedDelimiter != strDelimiter)
){
 
// Since we have reached a new row of data,
// add an empty row to our data array.
arrData.push( [] );
 
}
 
 
// Now that we have our delimiter out of the way,
// let's check to see which kind of value we
// captured (quoted or unquoted).
if (arrMatches[ 2 ]){
 
// We found a quoted value. When we capture
// this value, unescape any double quotes.
var strMatchedValue = arrMatches[ 2 ].replace(
new RegExp( "\"\"", "g" ),
"\""
);
 
} else {
 
// We found a non-quoted value.
var strMatchedValue = arrMatches[ 3 ];
 
}
 
 
// Now that we have our value string, let's add
// it to the data array.
arrData[ arrData.length - 1 ].push( strMatchedValue );
}
 
// Return the parsed data.
return( arrData );
}

 $(document).ready(function() {
    var refreshId = setInterval(function() {
        $.post('simant.php', function(data){
			var parseData = CSVToArray( data, ',' );
			$('#ants').html(parseData[0][0]);
			console.log(parseData[0][0]);
			$('#turnsum').prepend(parseData[0][1]);
			console.log(parseData[0][1]);
		});
    }, 5000);
    $.ajaxSetup({ cache: false });
});
</script><center>
<h1> Finite State Machine - Sim Ant</h1><br>
This little script is Sim-Ant, and me trying to get a grasp on Finite State Machines.<br>
While this script does have little AI, it shows the beginnings of one.<br>
The ants are able to search for food, and independently target different grains<br>
per ant. You start seeing it when there are more then 3 ants on the screen.<br><br>
(For best results, I suggest setting your browser zoom to ~60%)<br>
<br>
The rules of the sim are simple:<br><div style='text-align:left;width:500;'>
<?php include "rules.inc"; ?>
</div><br><br><a href='index.new.php'><button>Click Me to reset world</button></a><br><a href='https://bitbucket.org/Nymall/simant/overview'><button>Click Me to go to the Bitbucket Page</button></a><br><br>
<div id='ants' style='margin-left:auto;margin-right:auto;'>
</div><br><div id='turnsum' style='margin-left:auto;margin-right:auto;'><b><i>Setting up play area...</i></b></div><br><br><?php include "changelog.inc" ?></center>
</body>
</html>
</body>
</html>