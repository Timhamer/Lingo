<div class="container mx-auto p-4">
    <div>
        <h2>Top Scores</h2>
        <ul>
            @foreach($this->getTopScores() as $topscore)
                <li>{{ $topscore->username }} - {{ $topscore->score }}</li>
            @endforeach
        </ul>
    </div>
    @if($showNameInput)
    <form wire:submit.prevent="saveScore">
        <label for="userName">Enter Your Name:</label>
        <input type="text" wire:model="userName" id="userName" required>
        <button type="submit">Submit</button>
    </form>
    @endif

    <!-- Game Title -->
    <h1 class="text-4xl font-bold mb-8">Lingo Game</h1>

    <h2 class="text-4xl font-bold mb-8">{{ $score }}</h2>
    @if(!$showNameInput)
        <!-- Game Board -->
        <div id="gameBoard" class="grid grid-cols-5 gap-4 mt-6">
            @foreach($guessedLetters as $row)
                @foreach($row as $letter)
                <div class="p-4 w-32 h-32 text-center flex justify-center items-center text-2xl
                border-4 border-white rounded-3xl
                @if(isset($correctPositionFlags[$loop->parent->index][$loop->index]) && $correctPositionFlags[$loop->parent->index][$loop->index]) bg-green-500
                @elseif(isset($correctLetterFlags[$loop->parent->index][$loop->index]) && $correctLetterFlags[$loop->parent->index][$loop->index]) bg-red-500
                @else bg-blue-500
                @endif">
                {{ $letter }}
            </div>
            
            

                @endforeach
            @endforeach
        </div>

        <form wire:submit.prevent="checkGuess">
            <label for="guess">Enter your guess:</label>
            <input wire:model="userGuess" type="text" id="guess" name="guess" maxlength="5" minlength="5" autocomplete="off" required>
            <button type="submit">Submit Guess</button>
        </form>
    @endif
</div>
