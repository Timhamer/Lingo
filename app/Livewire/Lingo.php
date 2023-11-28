<?php

namespace App\Livewire;

use App\Models\Score;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Lingo extends Component
{
    private string $jsonPath = 'resources/json';
    public mixed $words = [];
    public array $word;
    public string $userGuess;
    public bool $isCorrect;
    public array $guessedLetters = [['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', '']];
    public int $currentRow = 0;
    public array $correctPositionFlags = [];
    public array $correctLetterFlags = [];
    public array $correctLetters = [];
    public $validationMessages = [];
    public int $score;
    public int $rowScore;
    public int $round = 1;
    public bool $showNameInput = false;
    public string $userName = '';

    public function mount()
    {
        $root = $_SERVER['DOCUMENT_ROOT'];
        
        $jsonFile = $root . '/../' . $this->jsonPath . '/words.json';

        $this->words = json_decode(file_get_contents($jsonFile), true)['words'];

        $word = array_rand($this->words);
        $word = strtoupper($this->words[$word]);
        $this->word = str_split($word);
        $this->guessedLetters[$this->currentRow][0] = $this->word[0];
        $this->correctPositionFlags[$this->currentRow][0] = true;
        $this->score = 0;
    }

    public function getTopScores()
    {
        return Score::orderBy('score', 'desc')->limit(10)->get();
    }

    public function newGame() {
        $this->round = 1;
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
        $this->score = 0;
    }

    public function newRound() {
        if ($this->round < 5) {
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
        } else {
            $this->showNameInput = true;
        }
    }

    public function checkGuess()
    {
        // Clear previous validation messages
        $this->validationMessages = [];
        $this->rowScore = 0;
        // Validate the user input
        $this->validate([
            'userGuess' => 'required|alpha|size:5',
        ]);

        // Check if the guess is correct
        $this->isCorrect = strtoupper($this->userGuess) === implode('', $this->word);

        // Update the guessedLetters array for the current row based on the correct positions of the guessed letters
        $guessedLettersArray = str_split(strtoupper($this->userGuess));

        // Track the occurrences of each letter in the word
        $occurrencesInWord = array_count_values($this->word);

        $unmatchedWord = $this->word;

        // Iterate through each letter in the user's guess
        foreach (str_split(strtoupper($this->userGuess)) as $index => $letter) {
        
        // Check if the letter is present in the word
        if (in_array($letter, $unmatchedWord)) {
            if ($letter === $this->word[$index]) {
                // Letter is in the correct position
                $this->correctPositionFlags[$this->currentRow][$index] = true;
                $this->rowScore += 20;
                if ($this->currentRow < 4) {
                $this->correctLetters[$index] = $letter;

                $this->guessedLetters[$this->currentRow+1][$index] = $letter;

            $this->correctPositionFlags[$this->currentRow+1][$index] = true;
                }
                unset($unmatchedWord[array_search($letter, $unmatchedWord)]);
            } 
        } else {
            $this->correctPositionFlags[$this->currentRow][$index] = false;
        }
            if (isset($this->correctLetters[$index])) {
            $this->guessedLetters[$this->currentRow+1][$index] = $this->correctLetters[$index];
            $this->correctPositionFlags[$this->currentRow+1][$index] = true;
            } else {
                $this->guessedLetters[$this->currentRow+1][$index] = "";
            $this->correctPositionFlags[$this->currentRow+1][$index] = false;
            }
        }

        foreach ($guessedLettersArray as $index => $letter) {
            $this->guessedLetters[$this->currentRow][$index] = $letter;

            if (in_array($letter, $unmatchedWord)) {// Check if the guessed letter is in the correct position
            if ($this->word[$index] === $letter) {
                $this->correctPositionFlags[$this->currentRow][$index] = true;
            } else {
                // Check if the letter occurs in the word
                if (in_array($letter, $this->word)) {
                    // Check if the letter is in the correct position
                    $correctPositionIndex = array_search($letter, $this->word);
                    if (!isset($this->correctPositionFlags[$this->currentRow][$correctPositionIndex])) {
                        // If not in the correct position, mark it as orange
                        $this->correctLetterFlags[$this->currentRow][$index] = true;
                        $this->rowScore += 10;
                    }
                }
            }

            // Check if we need to mark the letter as plain color
            if (
                $occurrencesInWord[$letter] === 1 
                && !isset($this->correctLetterFlags[$this->currentRow][$index]) 
                && !isset($this->correctPositionFlags[$this->currentRow][$index])
            ) {
                // If there's only one occurrence of the letter in the word, 
                // and it's not marked green or orange, mark it as plain color
                $this->correctLetterFlags[$this->currentRow][$index] = true;
            } else {
                if ($letter === $this->word[$index]) {
                    // Letter is in the correct position
                    $this->correctPositionFlags[$this->currentRow][$index] = true;
                } else {
                    $this->correctPositionFlags[$this->currentRow][$index] = false;
                    $this->correctLetterFlags[$this->currentRow][$index] = true;

                }
            }
        } else {
            if ($letter === $this->word[$index]) {
                // Letter is in the correct position
                $this->correctPositionFlags[$this->currentRow][$index] = true;
            } else {
                $this->correctPositionFlags[$this->currentRow][$index] = false;
                
            }
            // $this->correctLetterFlags[$this->currentRow][$index] = false;
        }
    }

    // Move to the next row
    if ($this->rowScore != 100) {
        $this->score += $this->rowScore;
        $this->currentRow++;
        if ($this->currentRow < 5) {
            $this->guessedLetters[$this->currentRow][0] = $this->word[0];
            $this->correctPositionFlags[$this->currentRow][0] = true;
        }
    } else {
        $this->score += $this->rowScore * (5-$this->currentRow);
        // sleep(2);
        // $this->dispatchBrowserEvent('delay-and-new-round');

        $this->newRound();
    }

    // Check if the maximum number of rows is reached
    if ($this->currentRow == 5) {
        for ($i=0; $i < $this->currentRow; $i++) { 
            $this->guessedLetters[$this->currentRow][$i] = $this->word[$i];
            $this->correctPositionFlags[$this->currentRow][$i] = true;
        }
        // sleep(3);
        // $this->dispatchBrowserEvent('delay-and-new-round');

    }
    
}

public function saveScore()
{
    // Insert the score and name into your SQLite database
    DB::table('scores')->insert(['username' => $this->userName, 'score' => $this->score]);

    // Reset properties for the next game
    $this->showNameInput = false;
    $this->userName = '';
    $this->newGame();
}

}
