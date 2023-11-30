<?php

namespace App\Livewire;

use App\Models\Score;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Lingo extends Component
{

    private string $jsonPath = 'resources/json'; //Path to json
    public mixed $words = []; //All the words
    public array $word; //Current word
    public string $userGuess; //The user's guess
    public array $guessedLetters = [['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', '']]; //The guessed letters for each row
    public int $currentRow = 0; //The amount of guess have taken place and which row they're at
    public array $correctPositionFlags = []; //An array with all the correct letter positions.
    public array $correctLetterFlags = []; //An array with all the correct letters and not positions
    public array $correctLetters = []; //An array with the correct letters to show in the next row.
    public int $score = 0; //This game user's score
    public int $rowScore; //The row score of the user
    public int $round = 1; //The current round
    public bool $showNameInput = false; //A bool to show the name input and hide the game
    public string $userName = ''; //The name the user entered in the input

    //On startup, fires
    public function mount()
    {
        //Get the words file
        $root = $_SERVER['DOCUMENT_ROOT'];

        $jsonFile = $root . '/../' . $this->jsonPath . '/words.json';

        //Make a php array of the words
        $this->words = json_decode(file_get_contents($jsonFile), true)['words'];

        //pick a random word from the words array, capatilize it and seperate the letters
        $word = array_rand($this->words);
        $word = strtoupper($this->words[$word]);
        $this->word = str_split($word);

        //Make the first letter visible and green
        $this->guessedLetters[$this->currentRow][0] = $this->word[0];
        $this->correctPositionFlags[$this->currentRow][0] = true;
    }

    //Get the top 10 scores from the database
    public function getTopScores()
    {
        return Score::orderBy('score', 'desc')->limit(10)->get();
    }

    //Reset variables to make a new game.
    public function newGame()
    {
        $this->newRound(true);
        $this->round = 1;
        $this->score = 0;
    }

    //Reset variables to make a new round. If you give a parameter, reset the round anyway
    public function newRound($newGame = NULL)
    {
        //Checks if the current round isn't at its max or if there is a parameter given
        if ($this->round < 5 || $newGame != NULL) {
            //Reset variables and pick a new word
            $this->round += 1;
            $word = array_rand($this->words);
            $word = strtoupper($this->words[$word]);
            $this->word = str_split($word);
            $this->currentRow = 0;
            $this->guessedLetters = [['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', '']];
            $this->guessedLetters[$this->currentRow][0] = $this->word[0];
            $this->correctPositionFlags = [];
            $this->correctPositionFlags[$this->currentRow][0] = true;
            $this->correctLetterFlags = [];
            $this->correctLetters = [];
        } else { //Else show the input and hide the game.
            $this->showNameInput = true;
        }
    }

    //Check every letter of the user's guess
    public function checkGuess()
    {
        $this->rowScore = 0;
        //Validate the guess of the user
        $this->validate([
            'userGuess' => 'required|alpha|size:5',
        ]);
        //Make another array with each letter of the word
        $unmatchedWord = $this->word;
        //For each letter in the user's guess
        foreach (str_split(strtoupper($this->userGuess)) as $index => $letter) {
            //If the guessed letter is inside the word and hasn't exceeded the amount in the word
            if (in_array($letter, $unmatchedWord)) {
                //If the letter is the same and in the same place
                if ($letter === $this->word[$index]) {
                    //Make the letter the correct position color
                    $this->correctPositionFlags[$this->currentRow][$index] = true;
                    //Increase the score
                    $this->rowScore += 20;
                    //If it isn't the last round
                    if ($this->currentRow < 4) {
                        //Make the letters visible in the next row
                        $this->correctLetters[$index] = $letter;

                        $this->guessedLetters[$this->currentRow + 1][$index] = $letter;

                        $this->correctPositionFlags[$this->currentRow + 1][$index] = true;
                    }
                    //Remove the letter from the temporary word array
                    unset($unmatchedWord[array_search($letter, $unmatchedWord)]);
                } else { //Else not make the letter the correct position color
                    $this->correctPositionFlags[$this->currentRow][$index] = false;
                }
            } else { //Else not make the letter the correct position color
                $this->correctPositionFlags[$this->currentRow][$index] = false;
            }
            //If the letter is correct and in the same position
            if (isset($this->correctLetters[$index])) {
                //Make the letter visible in the next row
                $this->guessedLetters[$this->currentRow + 1][$index] = $this->correctLetters[$index];
                $this->correctPositionFlags[$this->currentRow + 1][$index] = true;
            } else { //Else remove the correct position color property
                $this->guessedLetters[$this->currentRow + 1][$index] = "";
                $this->correctPositionFlags[$this->currentRow + 1][$index] = false;
            }
        }
        //For each letter in the user's guess
        foreach (str_split(strtoupper($this->userGuess)) as $index => $letter) {
            //Add the guessed letters to the guessedLetters array
            $this->guessedLetters[$this->currentRow][$index] = $letter;
            //If the letter is inside the temporary array
            if (in_array($letter, $unmatchedWord)) {
                //Make the letter the correct letter color
                $this->correctLetterFlags[$this->currentRow][$index] = true;
                //Increase the score
                $this->rowScore += 10;
                unset($unmatchedWord[array_search($letter, $unmatchedWord)]);
            } else { //If the letter isn't inside the temporary array
                //Remove the correct letter property
                $this->correctLetterFlags[$this->currentRow][$index] = false;
                //If the letter isn't inside the temporary array is incorrect
                if ($this->guessedLetters[$this->currentRow][$index] != $this->word[$index]) {
                    //Remove the correct position color
                    $this->correctPositionFlags[$this->currentRow][$index] = false;
                }
            }
        }
        //Check if the word is guessed
        if ($this->rowScore != 100) {
            $this->score += $this->rowScore;
            $this->currentRow++;
            //If it isn't the last row
            if ($this->currentRow < 5) {
                //Show the letters in the next row
                $this->guessedLetters[$this->currentRow][0] = $this->word[0];
                $this->correctPositionFlags[$this->currentRow][0] = true;
            } else { //It is the last row
                $this->newRound();
            }
        } else { //The word isn't guessed
            $this->score += $this->rowScore * (5 - $this->currentRow);
            $this->newRound();
        }
        //If it is the last row and not guessed the word, show a new row with the word
        if ($this->currentRow == 5) {
            for ($i = 0; $i < $this->currentRow; $i++) {
                $this->guessedLetters[$this->currentRow][$i] = $this->word[$i];
                $this->correctPositionFlags[$this->currentRow][$i] = true;
            }

        }
    }

    //Inserts the user's score and name to the database
    public function saveScore()
    {
        DB::table('scores')->insert(['username' => $this->userName, 'score' => $this->score]);

        $this->showNameInput = false;
        $this->userName = '';
        $this->newGame();
    }
}
