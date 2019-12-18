/*
 * Name: Pierce Buzhardt
 * Date: September 24, 2017
 * Lab Section: 003
 * Assignment: Homework 5
 */

package cpsc2150.hw5;


/**
 * Correspondence: this = board[0...MAX_SIZE-1][0...MAX_SIZE-1]
 * Correspondence: NUM_ROWS = row_Max
 * Correspondence: NUM_COLS = MAX_ARRAY_LENGTH_COL
 *
 * @invariant: 0 <= row_Max < MAX_SIZE
 * @invariant: 0 <= column_Max < MAX_SIZE
 */
public class GameBoardFast implements IGameBoard{

    private char board[][];
    private int row_Max;
    private int column_Max;
    private int NUM_FOR_WIN;

    /**
     *
     * @param max_Row maximum number of rows the board can use
     * @param max_Col maximum number of columns the board can use
     * @param win_Num number of symbols needed in a row to win
     * @requires 0 <= max_Row < MAX_SIZE
     * @requires 0 <= max_Col < MAX_SIZE
     * @requires 0 < win_Num <= max_Row && 0 < win_Num <= max_col
     * @Ensures creates a blank board of ' ' characters
     */
    public GameBoardFast(int max_Row, int max_Col, int win_Num){
        board = new char[MAX_SIZE][MAX_SIZE];
        column_Max = max_Col;
        row_Max = max_Row;
        NUM_FOR_WIN = win_Num;
        for(int i = 0; i < row_Max; i++){
            for(int j = 0; j < column_Max; j++){
                board[i][j] = ' ';
            }
        }

    }


    public boolean checkSpace(BoardPosition pos){
        if(pos.getRow() < 0 || pos.getRow()>= row_Max || pos.getColumn() < 0
                || pos.getColumn() >= column_Max)
            return false;
        if(board[pos.getRow()][pos.getColumn()] == ' ')
            return true;
        else
            return false;
    }

    public void placeMarker(BoardPosition marker){
        board[marker.getRow()][marker.getColumn()] = marker.getPlayer();
    }


    public boolean checkForWinner(BoardPosition lastPos){
        return (checkHorizontalWin(lastPos)||checkVerticalWin(lastPos)
                ||checkDiagonalWin(lastPos));
    }

    /**
     *
     * @param lastPos position of last play
     * @Requires lastPos exists
     * @Ensures returns if player won by horizontal placement
     */
    private boolean checkHorizontalWin(BoardPosition lastPos){
        int startPosRow = lastPos.getRow();
        int startPosCol = lastPos.getColumn();
        char lastChar = lastPos.getPlayer();
        int i = 1;
        int line = 1;

        while(startPosCol - i >= 0 && board[startPosRow][startPosCol-i]==lastChar){
            i++;
            line++;
            if(line >= NUM_FOR_WIN)
                return true;
        }
        i = 1;
        while(startPosCol + i < column_Max && board[startPosRow][startPosCol+i]==lastChar){
            i++;
            line++;
            if(line >= NUM_FOR_WIN)
                return true;
        }
        if(line >= NUM_FOR_WIN)
            return true;
        return false;
    }

    /**
     *
     * @param lastPos position of last play
     * @Requires lastPos exists
     * @ensure returns if player won by vertical placement
     */
    private boolean checkVerticalWin(BoardPosition lastPos){
        int startPosRow = lastPos.getRow();
        int startPosCol = lastPos.getColumn();
        char lastChar = lastPos.getPlayer();
        int i = 1;
        int line = 1;

        while(startPosRow - i >= 0 && board[startPosRow-i][startPosCol]==lastChar){
            i++;
            line++;
            if(line >= NUM_FOR_WIN)
                return true;
        }
        i = 1;
        while(startPosRow + i < row_Max && board[startPosRow+i][startPosCol]==lastChar){
            i++;
            line++;
            if(line >= NUM_FOR_WIN)
                return true;
        }
        if(line >= NUM_FOR_WIN)
            return true;
        return false;
    }

    /**
     *
     * @param lastPos position of last play
     * @Requires
     * lastPos exists and checkDiagonalWin == true or checkDiagonalWin == false
     * @Ensures returns if player won by diagonal placement
     */
    private boolean checkDiagonalWin(BoardPosition lastPos) {
        int startPosRow = lastPos.getRow();
        int startPosCol = lastPos.getColumn();
        char lastChar = lastPos.getPlayer();
        int i = 1;
        int line = 1;

        //Checks the forward diagonal by going space by space
        while(startPosRow - i >= 0 && startPosCol - i >= 0 && board[startPosRow-i][startPosCol-i]==lastChar){
            i++;
            line++;
            if(line >= NUM_FOR_WIN)
                return true;
        }
        i = 1;
        while(startPosRow + i < row_Max &&
                startPosCol + i < column_Max && board[startPosRow+i][startPosCol+i]==lastChar){
            i++;
            line++;
            if(line >= NUM_FOR_WIN)
                return true;
        }
        if(line >= NUM_FOR_WIN)
            return true;

        line = 1;
        i = 1;

        //checks the reverse diagonal
        while(startPosRow - i >= 0 &&
                startPosCol + i < column_Max && board[startPosRow-i][startPosCol+i]==lastChar){
            i++;
            line++;
            if(line >= NUM_FOR_WIN)
                return true;
        }
        i = 1;
        while(startPosRow + i < row_Max &&
                startPosCol - i >= 0 && board[startPosRow+i][startPosCol-i]==lastChar){
            i++;
            line++;
            if(line >= NUM_FOR_WIN)
                return true;
        }
        if(line >= NUM_FOR_WIN)
            return true;

        return false;
    }

    /**
     *
     * @return a board in the format: <pre>
     *        0 1 2 3 4 5 6 7
            0| | | | | |X| | |
            1| | |O| | |X| | |
            2| | | |O| | | | |
            3| | | | | | | | |
            4| | | | | | | | |
            5| | | | | | | | |
            6| | | | | | | | |
            7| | | | | | | | |
     * </pre>
     * @Requires GameBoard instance exists
     * @Ensures toString shows the board
     */
    @Override
    public String toString(){
        String boardString = "";
        int i = 0;
        for(; i < row_Max+1; i++) {
            for (int j = 0; j < column_Max+1; j++) {
                if (i == 0){
                    if(j==0){
                        boardString = boardString + "   ";
                    }
                    if(j < column_Max) {
                        boardString = boardString + String.format("%1$2s", j) + " ";
                    }
                }
                else{
                    if(j==0){
                        boardString = boardString + String.format("%1$2s", (i-1)) + "|";
                    }
                    else{
                        boardString = boardString + String.format("%1$2s", board[i-1][j-1]) + "|";
                    }
                }
            }
            boardString = boardString + "\n";
        }
        return boardString;
    }
}
