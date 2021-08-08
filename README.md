# anagram

Simple anagram game using PHP and Vue JS

There are two versions:

- Picks a random word from a text file
- MySQL version

The word list from which the anagrams are taken is from a bunch of Gutenburg texts. The wordlist, both text and database version is produced by running loadwords.php (database and table must exist already). The anagram app itself picks a random word from the text file or the database and presents it in a scrambled form.

Uses the [Words API from Mashape](https://market.mashape.com/wordsapi/wordsapi) to get a definition for the word, if available, and shows this as a clue if required. In the database version the clue is stored in the database so the API does not have to be called a second time for the same word. This [API is free](https://www.wordsapi.com/) up to 2500 request per day, at August 2018.

It is just a simple project to demonstrate some basic skills.

## Features

- basic use of object oriented PHP and PDO
- [Vue-Draggable](https://github.com/SortableJS/Vue.Draggable) and [SortableJS](https://github.com/RubaXa/Sortable) as well as Vue,
- Button styling from [Purecss](https://purecss.io/buttons/).
- Vue JS modal component from Vue JS [example section](https://vuejs.org/v2/examples/modal.html)
- Conditional rendering with `v-if`
- Looping with `v-for`
- Uses LocalStorage to saved data on anagrams successfully solved
- Uses [this fiddle](http://jsfiddle.net/gfullam/sq9U7/) as `compareFunction` for `array.sort` to allow the display of solved words to be sorted alphabetically, by time taken to solve, by length, and by date/time
- Uses CSS grid and flexbox but very basically
- responsiveness is executed purely by using viewport units `vh` and `vw` along with the odd percentage.

A much earlier version of this app used a web worker to keep track of the time and test if the solution had been found, but this is replaced here by using the `watch: {}` construct in Vue JS
