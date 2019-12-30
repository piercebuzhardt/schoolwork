/* smpl-unify1-gui.pro */

/* goal to start debugging */

startDebug :- /* what to trace */
spy(first), spy(second),
/* turn on guitracer for trace */
guitracer.

goal1(X,Y) :- 
/* original goal1 (logically unmodified) */
first(X), second(Y).

goal2(X) :- 
/* original goal2 (logically unmodified) */
first(X), second(X).

first(1).
first(2).
first(3).
second(2).
second(4).
second(6).
