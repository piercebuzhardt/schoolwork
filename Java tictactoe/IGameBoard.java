/*
  Name: Pierce Buzhardt
  Date: October 26, 2017
  Section: 001
  Assignment: Homework 5
 */


package cpsc2150.hw5;


/**
 * IGameBoard represents a 2-dimensional gameboard that has characters
 * on it as markers (X, O). No space on the board can have multiple
 * players, and there can be a clear winner. Board is NUM_ROWS x NUM_COLS in size
 *
 * Initialization ensures: the Board does not have any markers on it
 * Defines: NUM_ROWS: Z
 * Defines: NUM_COLS: Z
 * Constraints: 0< NUM_ROWS <= MAX_SIZE
 * Constraints: 0< NUM_COLS <= MAX_SIZE
 */
public interface IGameBoard {
    public static final int MAX_SIZE = 100;
    // add your contracts

    /**
     *
     * @param pos a BoardPosition detailing a position
     * @requires some implementation of GameBoard is instantiated
     * @return if the location specified in pos is available
     */
    boolean checkSpace(BoardPosition pos);
    // add your contracts

    /**
     *
     * @param lastPos BoardPosition that has resulted in true for checkSpace
     * @requires checkSpace(lastPos) == true
     * @ensures lastPos is placed on GameBoard
     */
    void placeMarker(BoardPosition lastPos);
    // add your contracts

    /**
     *
     * @param lastPos BoardPosition that was placed by placeMarker
     *
     * @return if lastPos resulted in a win
     */
    boolean checkForWinner(BoardPosition lastPos);
}

