<!doctype html>
<html lang="en">
<?php

  // Require composer autoloader
  require __DIR__ . '/vendor/autoload.php';

$fname="words.txt";
$myfile = fopen($fname, "r") or die("Unable to open file!");
$buffer = fread($myfile,filesize($fname));
fclose($myfile);
if (strlen($buffer))  {
    $words = str_word_count($buffer,1);
    $rand_key = array_rand($words, 1);
    $unscrambled = $words[$rand_key];
    $scrambled = scramble($unscrambled);
    $url = "https://wordsapiv1.p.mashape.com/words/" . $unscrambled;
    $response = Unirest\Request::get($url,
  	array(
    		"X-Mashape-Key" => "insert key here",
    		"Accept" => "application/json"
  	)
    );
    $clue = '';
    if($response->code === 200) {
    	$result = $response->body->results[0];
	$clue = $result->definition;
    }
    if(strlen($clue) == 0){
    	$clue = "No clue available";
    }
}
else
{
    echo  "no buffer";
}
function scramble($inStr)
{
  $outStr = "";
  $in_idx = array();
  for ($j = 0; $j < strlen($inStr); $j++)
    {
	    $in_idx[$j] = $j;
	}
  $out_idx = array();

  while (true)
	{
 	$new_idx = mt_rand(1,sizeOf($in_idx))-1;
 	$unique = 1;							

	for ($k = 0; $k < sizeOf($out_idx); $k++)
		{
		if ($in_idx[$new_idx] == $out_idx[$k])
			{
			$unique = 0;					
			}
		}
	if ($unique == 1)
		{
		array_push($out_idx,$in_idx[$new_idx]);
		if (sizeof($in_idx) == 1)
			{
			break;
			}
		array_splice($in_idx,$new_idx,1);
		}
	}

  for ($i = 0; $i < sizeOf($out_idx); $i++)
	{
	$outStr .= $inStr{$out_idx[$i]};
	}
  return $outStr;
}
?>

<head>
    <title>Anagram</title>
    <meta charset="utf-8">
    <meta name="description" content="Anagram">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.7.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.16.0/vuedraggable.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/buttons-min.css">
</head>

<body>
	<!-- template for the modal component -->
	<script type="text/x-template" id="modal-template">
	  <transition name="modal">
	    <div class="modal-mask">
	      <div class="modal-wrapper">
	        <div class="modal-container">
	
	          <div class="modal-header">
	            <slot name="header">
	              default header
	            </slot>
	          </div>
	
	          <div class="modal-body">
	            <slot name="body">
	              '<?php echo $clue; ?>'
	            </slot>
	          </div>
	
	          <div class="modal-footer">
	            <slot name="footer">
	              Click to close
	              <button class="modal-default-button" @click="$emit('close')">
	                OK
	              </button>
	            </slot>
	          </div>
	        </div>
	      </div>
	    </div>
	  </transition>
	</script>
	
    <div id="app" class="container">
        <div v-if="finished == 0" class="wrapper">
            <draggable class="grid-item" :list="scrambledArray" @start="dragging=true" @end="dragging=false">
                <div class="box" v-for="(letter, index) in scrambledArray" :key="index" v-on:click="solutionInput($event, index)">{{letter.letter}}</div>
            </draggable>
        </div>
        <div v-if="solved" class="display">{{ successDisplay }}</div>
        <div v-else id="displayInput" class="display">{{ inputDisplay }}</div>
        <div class="grid-item">
            <button class="pure-button custom-button" v-on:click="reset()">Reset</button>
            <button v-if="cheated === false" class="pure-button custom-button" v-on:click="cheat()">Cheat</button>
            <button v-if="solved === false" class="pure-button custom-button" v-on:click="scramble()">Scramble</button>
            <button class="pure-button custom-button" onclick="window.location.reload()">Get New</button>
	    <button v-if="solved === false" id="show-modal" class="pure-button custom-button" v-on:click="showModal = true">Show Clue</button>
        </div>
        <section>
            <table v-if="solves.length">
                <tr>
                    <th style="text-align:left"><button class="pure-button column-button" v-on:click="wordSort()">Word</button></th>
                    <th style="text-align:right"><button class="pure-button column-button" v-on:click="lengthSort()">Length</button></th>
                    <th style="text-align:right"><button class="pure-button column-button" v-on:click="elapsedSort()">Time to Solve</button></th>
                    <th style="text-align:right"><button class="pure-button column-button" v-on:click="clear()">Forget Solves</button> <button class="pure-button column-button" v-on:click="whenSort()">When Solved</button></th>
                </tr>
                <tr v-for="solve in solves">
                    <td style="text-align:left">{{solve.word}}</td>
                    <td style="text-align:right">{{solve.word.length}}</td>
                    <td style="text-align:right">{{solve.elapsed}}</td>
                    <td style="text-align:right">{{solve.when}}</td>
                </tr>
            </table>
        </section>
        <modal v-if="showModal" @close="showModal = false">
        <h3 slot="header">Clue:</h3>
        </modal>
    </div>

    <script>
        var STORAGE_KEY = 'anagram-vuejs-2.0'
        var solveStorage = {
            fetch: function () {
                var solves = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
                solves.forEach(function (solve, index) {
                    solve.id = index
                })
                solveStorage.uid = solves.length
                return solves
            },
            save: function (solves) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(solves))
            },
        }
                
        /* "Sort by, then by"
        taken from this jsfiddle: http://jsfiddle.net/gfullam/sq9U7/
        Description: Sort by object key, with optional reverse ordering, priming, and 'then by' sorting.
        
            array.sort(
                by(path[, reverse[, primer[, then]]])
            );
        */

        /* THE FUNCTION */
        var by = function (path, reverse, primer, then) {
            var get = function (obj, path) {
                if (path) {
                    path = path.split('.');
                    for (var i = 0, len = path.length - 1; i < len; i++) {
                        obj = obj[path[i]];
                    };
                    return obj[path[len]];
                }
                return obj;
            },
                prime = function (obj) {
                    return primer ? primer(get(obj, path)) : get(obj, path);
                };

            return function (a, b) {
                var A = prime(a),
                    B = prime(b);

                return (
                    (A < B) ? -1 :
                        (A > B) ? 1 :
                            (typeof then === 'function') ? then(a, b) : 0
                ) * [1, -1][+!!reverse];
            };
        };
        // register modal component
	Vue.component('modal', {
	  template: '#modal-template'
	})
        var app = new Vue({
            el: '#app',
            data: {
                scrambled: '<?php echo $scrambled; ?>',
                unscrambled: '<?php echo $unscrambled; ?>',
                clue: '<?php echo $clue; ?>',
                scrambledArray: [
                    <?php 
                    for ($i = 0; $i < strlen($scrambled); $i++){
	 	                echo(" {letter: '".substr($scrambled,$i,1)."'},");
	                }?>
                ],
                solves: solveStorage.fetch(),
                inputDisplay: '',
                successDisplay: '',
                solved: false,
                started: 0,
                finished: 0,
                cheated: false,
                dragging: false,
                wordAscending: false,
                lengthAscending: false,
                elapsedAcending: false,
                whenAscending: false,
                showModal: false
            },
            mounted: function() {
            	this.started = Date.now();
            },
            watch: {
                inputDisplay: {
                    handler: function (inputDisplay) {
                        if (inputDisplay == this.unscrambled) {
                            this.solved = true;
                            this.cheated = true; //did not cheat but don't show cheat button anyway
                            this.finished = Date.now();
                            var date = new Date(this.finished);
                            this.successDisplay = 'Well Done, You solved the Anagram in ' + (this.finished - this.started) + ' ms.'
                            this.solves.push({
                                word: this.unscrambled,
                                length: this.unscrambled.length,
                                elapsed: this.finished - this.started,
                                when: date.toISOString().substring(0, 10) + ' ' + date.toLocaleTimeString()
                            })
                        }
                    },
                },
                solves: {
                    handler: function (solves) {
                        solveStorage.save(solves)
                    },
                    deep: true
                }
            },
            methods: {
                solutionInput: function (event, index) {
                    if (event) {
                        this.inputDisplay = this.inputDisplay + event.target.innerText;
                        this.scrambledArray.splice(index, 1)
                    }
                },
                reset: function () {
                    this.inputDisplay = '';
                    this.scrambledArray = [];
                    var letters = this.scrambled.length;
                    for (var i = 0; i < letters; i++) {
                        this.scrambledArray.push({
                            letter: this.scrambled.charAt(i)
                        });
                    }
                },
                cheat: function () {
                    this.scrambledArray = [];
                    this.solved = false;
                    this.finished = Date.now();
                    if (this.started) {
                        this.inputDisplay = 'The word was ' + this.unscrambled + '. You gave up after ' + (this.finished - this.started) + ' ms';
                    }
                    else {
                        this.inputDisplay = 'The word was ' + this.unscrambled;
                    }
                    this.cheated = true;
                },
                scramble: function () {
                    this.scrambledArray.sort(function (a, b) { return 0.5 - Math.random() });
                },
                clear: function () {
                    this.solves = [];
                },
                wordSort: function () {
                    if(this.wordAscending){
                    	this.wordAscending = false;
                    	this.solves.sort(by('word', true));
                    }
                    else{
                    	this.wordAscending = true;
                    	this.solves.sort(by('word'));
                    }
                },
                lengthSort: function () {
                    if(this.lengthAscending){
                    	this.lengthAscending = false;
                    	this.solves.sort(by('length', true));
                    }
                    else{
                    	this.lengthAscending = true;
                    	this.solves.sort(by('length'));
                    }
                },                
                elapsedSort: function () {
                    if(this.elapsedAscending){
                    	this.elapsedAscending = false;
                    	this.solves.sort(by('elapsed', true));
                    }
                    else{
                    	this.elapsedAscending = true;
                    	this.solves.sort(by('elapsed'));
                    }
                },    
                whenSort: function () {
                    if(this.whenAscending){
                    	this.whenAscending = false;
                    	this.solves.sort(by('when', true));
                    }
                    else{
                    	this.whenAscending = true;
                    	this.solves.sort(by('when'));
                    }
                }                            
            }
        })

    </script>
    <style>
        body {
            font-family: Verdana, Arial, sans-serif;
            display: grid;
            grid-template-columns: 2% 96% 2%;
            align-content: center;
            align-items: center;
        }

        .container {
            grid-column: 2;
        }

        .grid-item {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }

        .wrapper {
            margin-top: 2vh;
            margin-bottom: 2vh;
            background: darkgray;
            padding: 2vw;
        }

        .box {
            font-size: 6vw;
            min-width: 7.8vw;
            text-align: center;
            margin-right: 1vw;
            margin-top: 1vw;
            cursor: pointer;
            background-color: green;
            color: white;
            border: 1px solid #aaaaaa;
            line-height: 8vw;
        }

        .display {
            width: 100%;
            text-align: center;
            font-size: 6vw;
            margin-bottom: 5vh;
            margin-top: 5vh;
        }

        .custom-button {
            border-radius: 1vh;
            background: rgb(6, 78, 160);
            /* this is a dark blue */
            color: white;
            font-size: 2.5vw;
            margin-right: 1vw;
            margin-left: 1vw;
        }

        .column-button {
            background: rgb(64, 160, 103);
            /* this is a green */
            color: white;
            font-size: 2vw;
        }

        section {
            margin-top: 3vh;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #000;
            text-align: left;
            padding: 0.5vw;
            font-size: 3vw;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
        
        .modal-mask {
	  position: fixed;
	  z-index: 9998;
	  top: 0;
	  left: 0;
	  width: 100%;
	  height: 100%;
	  background-color: rgba(0, 0, 0, .5);
	  display: table;
	  transition: opacity .3s ease;
	}
	
	.modal-wrapper {
	  display: table-cell;
	  vertical-align: middle;
	}
	
	.modal-container {
	  width: 300px;
	  margin: 0px auto;
	  padding: 20px 30px;
	  background-color: #fff;
	  border-radius: 2px;
	  box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
	  transition: all .3s ease;
	  font-family: Helvetica, Arial, sans-serif;
	}
	
	.modal-header h3 {
	  margin-top: 0;
	  color: #42b983;
	}
	
	.modal-body {
	  margin: 20px 0;
	}
	
	.modal-default-button {
	  float: right;
	}
	
	/*
	 * The following styles are auto-applied to elements with
	 * transition="modal" when their visibility is toggled
	 * by Vue.js.
	 *
	 * You can easily play with the modal transition by editing
	 * these styles.
	 */
	
	.modal-enter {
	  opacity: 0;
	}
	
	.modal-leave-active {
	  opacity: 0;
	}
	
	.modal-enter .modal-container,
	.modal-leave-active .modal-container {
	  -webkit-transform: scale(1.1);
	  transform: scale(1.1);
	}
    </style>
</body>