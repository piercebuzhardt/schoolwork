/* smpl-unify1-gui2.pro */

/* goal to start debugging */

startDebug :- /* what to trace */
spy(first), spy(second), spy(goal1), spy(goal2),
/* turn on guitracer for trace */
guitracer.

/* now the previous goals */

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
