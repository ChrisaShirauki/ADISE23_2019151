let selectedBlock = null; // Store the selected block
let selectedColor = null; // Store the color of the selected block

let playerToken = null; // To store the player's token
let playerColor = null; // To store the player's color

let playerTurn = null;
let gameStatus = null;

let shapesData = null;

let rotation = 0;

// Add an event listener to the "Find Game" button
document.getElementById('find-game-button').addEventListener('click', async () => {
    const username = document.getElementById('username').value.trim();

    if (!username) {
        alert('Please enter a username!');
        return;
    }

    try {
        const response = await fetch('blokus.php/game', { // Replace with your API endpoint
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username: username }),
        });

        if (!response.ok) {
            const errorData = await response.json(); // Parse the error response
            alert(errorData.message || 'An unexpected error occurred.');
            return; // Stop further execution
        }

        const data = await response.json();
        playerToken = data.token; // Store the token
        playerColor = data.color; // Store the color

        // Update the UI to show the color and token
        document.getElementById('player-color').textContent = playerColor;
        document.getElementById('player-token').textContent = playerToken;

        alert(`Game found! You are player ${playerColor}.`);
        document.getElementById("form-container").style.display = "none";
        fetchAllData();
        startPolling();
    } catch (error) {
        console.error('Error finding a game:', error);
    }
});

document.getElementById('leave-game-button').addEventListener('click', async () => {

    try {
        const response = await fetch('blokus.php/game', { // Replace with your API endpoint
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                color: playerColor,
                token: playerToken 
            }),
        });

        if (!response.ok) {
            const errorData = await response.json(); // Parse the error response
            alert(errorData.message || 'An unexpected error occurred.');
            return; // Stop further execution
        }
        fetchAllData();
    } catch (error) {
        console.error('Error leaving a game:', error);
    }
});

// Fetch board data and draw the board
async function fetchBoard() {
    try {
        const response = await fetch('blokus.php/board'); // Replace with your API URL
        const boardData = await response.json();
        drawBoard(boardData);
    } catch (error) {
        console.error("Error fetching board data:", error);
    }
}

function drawBoard(board) {
    const boardContainer = document.getElementById('game-board');
    boardContainer.innerHTML = ''; // Clear existing content

    for (const rowKey in board) {
        const row = board[rowKey];
        for (const cellKey in row) {
            const cell = row[cellKey];
            const cellDiv = document.createElement('div');
            cellDiv.classList.add('cell');
            cellDiv.dataset.x = parseInt(rowKey) + 1; // Store x-coordinate
            cellDiv.dataset.y = parseInt(cellKey) + 1; // Store y-coordinate

            // Add classes based on the piece color
            if (cell.piece_color === 'R') {
                cellDiv.classList.add('red');
            } else if (cell.piece_color === 'B') {
                cellDiv.classList.add('blue');
            }else if (cell.piece_color === 'Y') {
                cellDiv.classList.add('yellow');
            }else if (cell.piece_color === 'G') {
                cellDiv.classList.add('green');
            } else {
                cellDiv.classList.add('empty');
            }

            // Optionally display the piece name
            if (cell.piece) {
                cellDiv.textContent = cell.piece;
            }

            cellDiv.addEventListener('click', () => placeBlock(cellDiv)); // Add click event
            boardContainer.appendChild(cellDiv);
        }
    }
}

// Fetch player blocks and display them
async function fetchPlayerBlocks() {
    try {
        const response = await fetch('blokus.php/blocks'); // Replace with your API URL
        const blocksData = await response.json();
        drawPlayerBlocks(blocksData);
    } catch (error) {
        console.error("Error fetching player blocks:", error);
    }
}

function drawPlayerBlocks(blocks) {
    const blocksContainer = document.getElementById('player-blocks');
    blocksContainer.innerHTML = '<h2>Player Blocks</h2> <span>Pick your block type</span>'; // Reset content

    for (const player in blocks) {
        const playerBlockContainer = document.createElement('div');
        playerBlockContainer.classList.add('block-container');
        const playerTitle = document.createElement('h3');
        playerTitle.textContent = `Player ${player}`;

        for (const block of blocks[player]) {
            const blockDiv = document.createElement('div');
            blockDiv.classList.add('block');

            // Add player-specific class for colors
            if (player === 'R') {
                blockDiv.classList.add('red');
            } else if (player === 'B') {
                blockDiv.classList.add('blue');
            } else if (player === 'G') {
                blockDiv.classList.add('green');
            } else if (player === 'Y') {
                blockDiv.classList.add('yellow');
            }

            blockDiv.textContent = block; // Display block ID or data

            if (player === playerColor){
            blockDiv.addEventListener('click', () => {
                selectBlock(block, player, blockDiv);
            }); // Add click event
            }

            playerBlockContainer.appendChild(blockDiv);
        }

        blocksContainer.appendChild(playerTitle);
        blocksContainer.appendChild(playerBlockContainer);
    }
}

// Handle block selection
function selectBlock(block, color, blockElement) {
    // Deselect previously selected block
    const previouslySelected = document.querySelector('.block.selected');
    if (previouslySelected) {
        previouslySelected.classList.remove('selected');
    }

    // Select the clicked block
    selectedBlock = block;
    selectedColor = color;
    renderBlockPreview(shapesData[selectedBlock]);
    blockElement.classList.add('selected');
}

// Handle placing the block on the board
async function placeBlock(cell) {
    if (selectedBlock === null || selectedBlock === undefined || !selectedColor) {
        alert('Please select a block first!');
        return;
    }

    const x = parseInt(cell.dataset.x);
    const y = parseInt(cell.dataset.y);

    try {
        const response = await fetch('blokus.php/place', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                x: x,
                y: y,
                piece: selectedBlock,
                color: selectedColor,
                rotation: rotation,
                token: playerToken
            }),
        });

        if (!response.ok) {
            const errorData = await response.json(); // Parse the error response
            alert(errorData.message || 'An unexpected error occurred.');
            return; // Stop further execution
        }

        // Re-fetch the board to reflect the new state
        fetchAllData();
    } catch (error) {
        console.error("Error placing the block:", error);
    }
}

async function fetchShapes() {
    try {
        const response = await fetch('blokus.php/shapes'); // Replace with your endpoint
        if (!response.ok) throw new Error('Failed to fetch shapes');
        shapesData = await response.json();
    } catch (error) {
        console.error('Error fetching shapes:', error);
    }
}

function renderBlockPreview(shapeArray) {
    const selectedBlockDiv = document.getElementById('selected-block');
    selectedBlockDiv.innerHTML = ''; // Clear previous block preview

    // Rotate the shape array based on the rotation value
    const rotatedShape = rotateArray(shapeArray, rotation);

    // Set up a grid display for the shape
    const rows = rotatedShape.length;
    const cols = rotatedShape[0].length;
    selectedBlockDiv.style.display = 'grid';
    selectedBlockDiv.style.gridTemplateRows = `repeat(${rows}, 1fr)`;
    selectedBlockDiv.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;

    // Render the shape using divs
    for (const row of rotatedShape) {
        for (const cell of row) {
            const blockDiv = document.createElement('div');
            blockDiv.classList.add('block');
            if (playerColor === 'R') {
                blockDiv.classList.add('red');
            } else if (playerColor === 'B') {
                blockDiv.classList.add('blue');
            } else if (playerColor === 'G') {
                blockDiv.classList.add('green');
            } else if (playerColor === 'Y') {
                blockDiv.classList.add('yellow');
            }
            if (cell === 0) {
                blockDiv.classList.add('empty');
            }
            selectedBlockDiv.appendChild(blockDiv);
        }
    }
}

function rotateArray(array, rotations) {
    let rotatedArray = array;
    for (let i = 0; i < rotations; i++) {
        rotatedArray = rotateClockwise(rotatedArray);
    }
    return rotatedArray;
}

function rotateClockwise(array) {
    const rows = array.length;
    const cols = array[0].length;
    const rotated = Array.from({ length: cols }, () => Array(rows).fill(0));

    for (let row = 0; row < rows; row++) {
        for (let col = 0; col < cols; col++) {
            rotated[col][rows - 1 - row] = array[row][col];
        }
    }

    return rotated;
}

// Add right-click event to rotate the block
document.addEventListener('contextmenu', (event) => {
    if (!selectedBlock) return;

    event.preventDefault(); // Prevent the default context menu
    rotation = (rotation + 1) % 4; // Increment rotation and wrap around at 4
    renderBlockPreview(shapesData[selectedBlock]); // Re-render with the new rotation
});

let gameInterval = null;

async function fetchGameStatus() {
    try {
        const response = await fetch('blokus.php/game'); // Replace with your API endpoint
        if (!response.ok) {
            console.error('Failed to fetch game status');
            return;
        }

        const data = await response.json();
        updateGameStatus(data[0]);
    } catch (error) {
        console.error('Error fetching game status:', error);
    }
}

function updateGameStatus(data) {
    if (data.status === 'ABORTED') {
        alert('Game was aborted.');
        stopPolling(); // Stop polling if the game is aborted
        return;
    }

    // Update the game status and player turn on the page
    gameStatus = data.status;
    playerTurn = data.player;

    document.getElementById("player-turn").innerHTML = playerTurn;
}

async function fetchAllData() {
    // Fetch game status, board, and player blocks in parallel
    await Promise.all([
        fetchGameStatus(),
        fetchBoard(),
        fetchPlayerBlocks(),
    ]);
}

function startPolling() {
    // Start polling every 5 seconds
    pollingInterval = setInterval(fetchAllData, 5000);
}

function stopPolling() {
    // Stop polling
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

// Fetch and render the board and blocks
document.addEventListener('DOMContentLoaded', fetchShapes);
document.addEventListener('DOMContentLoaded', fetchAllData);

// Start polling when the page loads
document.addEventListener('DOMContentLoaded', () => {
    startPolling();
});

