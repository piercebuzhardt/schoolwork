/*
  Name: Pierce Buzhardt
  Date: November 27, 2017
  Section: 001
  Assignment: Homework 5
 */

package cpsc2150.hw5;

/**
 * The TicTacToe controller class will handle communication between our TicTacToeView and our Model (IGameBoard and BoardPosition)
 *
 * This is where you will write code
 *
 * You will need to include your BoardPosition class, the IGameBoard interface
 * and one of the IGameBoard implementations from Homework 4
 * You can choose which IGameBoard implementation to use
 * If your code was correct you will not need to make any changes to your IGameBoard implementation class besides the package name
 */
public class TicTacToeController {
    //our current game that is being played
    private IGameBoard curGame;
    //to track who's turn it is
    private char curPlayer = 'X';
    //The screen that provides our view
    private TicTacToeView screen;
    // no other class variables are needed or should be declared


    // add the code and contracts for the constructor of our tic tac toe game

    /**
     * Constructor; connects (@code this) to the model and view it coordinates
     *
     * @param model model to connect to
     * @param view view to connect to
     */
    TicTacToeController(IGameBoard model, TicTacToeView view)
    {
        this.curGame = model;
        this.screen = view;

    }

    //Add the code to respond to a user clicking on a button to try to claim a space
    //User will click on the button at row, col
    //User may click on a space that is not available, they do not get that space
    //We no longer have a main function that controls the flow of the game. Users will click buttons, anf
    //Will handle the events here
    // Make sure to make any changes to the screen needed through publicly available functions
    // When a player wins, display a message. You do not need to reset the game or close the window
    // The players can close the window and restart the game themselves
    // remember your javadoc comments and contracts

    /**
     *
     * @requires button was pressed on GUI
     * @ensures button shows player's character if available
     * @param row integer row of button that was pressed
     * @param col integer column of button that was pressed
     */
    public void processButtonClick(int row, int col) {

        BoardPosition pos = new BoardPosition(row, col, curPlayer);
        if (this.curGame.checkSpace(pos)) {
            this.curGame.placeMarker(pos);
            this.screen.setMarker(row, col, curPlayer);
            if (this.curGame.checkForWinner(pos)) {
                this.screen.setMessage(curPlayer + " has won.");

            } else {

                if (curPlayer == 'X')
                    curPlayer = 'O';

                else
                    curPlayer = 'X';

                this.screen.setMessage("It is " + curPlayer + "\'s turn. ");
            }
        } else{
            this.screen.setMessage("Position already taken.\n" +
                    "Still " + curPlayer + "\'s turn.");
        }
    }
}
