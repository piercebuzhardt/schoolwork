/*
 * Name: Pierce Buzhardt
 * Date: September 24, 2017
 * Lab Section: 003
 * Assignment: Homework 5
 */

package cpsc2150.hw5;


/**
 * @Invariant c == 'X' || c == 'O'
 */
public class BoardPosition {

    private int row;
    private int col;
    private char c;

    /**
     *
     * @param row row
     * @param col col
     * @param c player letter
     * @requires row instanceof int, col instanceof int, c instanceof char
     * @ensures this.row = row, this.col = col, this.c = c
     */
    public BoardPosition(int row, int col, char c){
        this.row = row;
        this.col = col;
        this.c = c;
    }

    /**
     *
     * @return x
     * @requires x != NULL
     * @ensures getRow = row
     */
    public int getRow(){
        return this.row;
    }

    /**
     *
     * @return y
     * @requires y != NULL
     * @ensures getColumn = col
     */
    public int getColumn(){
        return this.col;
    }

    /**
     *
     * @return c
     * @invariant c == 'X' || c == 'O'
     * @ensures getPlayer = c
     */
    public char getPlayer(){
        return this.c;
    }

    /**
     * @Override
     * @param obj
     * @return if this is equal to obj
     */
    public boolean equals(Object obj){
        if(!(obj instanceof BoardPosition)){
            return false;
        }
        BoardPosition pos = (BoardPosition) obj;

        if(pos.getColumn()==this.getColumn() && pos.getRow()==this.getRow() && pos.getPlayer()==this.getPlayer())
            return true;
        else
            return false;
    }
}
