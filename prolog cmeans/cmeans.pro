/* Pierce Buzhardt */
/* CPSC 3520 */
/* cmeans.pro */
/* SDE3 */

/**
Prototype:
distanceR2(+V1,+V2,-Dsq)
Arguments: vectors V1 and V2 (as lists), Dsq is result
Notes:
1. A necessary capability is, given a vector, to be able to find the closest
vector in another set of vectors.
The distance between any two vectors is computed by forming the difference vector
and then taking the square root of the inner product of this difference vector
with itself. It is also the square root of the sum of the squared elements of the
difference vector. However, since we are interested in minimum distances
(which correspond to minima of distances squared), we leave out the square root computation.
2. This may be done recursively, element-by-element.
*/
p([H|T], H, T).

distanceR2(V1, V1, 0).

distanceR2(V1, V2, Dsq) :- 
	p(V1, X, Y),
	p(V2, Z, A),
	distanceR2(Y, A, Q),
	Dsq is (X - Z) * (X - Z) + Q,
	!.
	
/**
Prototype:
distanceSqAllMeans(+V,+Vset,-Dsq)
Arguments: a vector and a set of vectors (represented as lists), and a vector of the
distances from v to each element of vset.
Notes: The objective is to take a single vector, V, and a set of vectors (to be the
current means in list-of-lists form) and compute the distance squared from V to each
of the vectors in the given set. The result is a list of squared distances.
*/

distanceSqAllMeans(_, [], []).

distanceSqAllMeans(V, [HA|TA], [HD|TD]) :-
	distanceR2(V, HA, HD),
	distanceSqAllMeans(V, TA, TD),
	!.
	
/**
Prototype: listMinPos(+Alist,-M)
Arguments: Alist, M is position (0-based indexing) of the minimum in the list
*/

listMin([HA|[]],HA):-
	!.

listMin([HA|TA], K) :-
	listMin(TA, Min),
	K is min(HA, Min),
	!.

findMin([HA|_], HA, Pos, X):-
	X is Pos,
	!.

findMin([_|TA], Min, Pos, X):-
	Next is Pos + 1,
	findMin(TA, Min, Next, X),
	!.
	
listMinPos(Alist, M) :-
	listMin(Alist, Min),
	Pos is 0,
	findMin(Alist, Min, Pos, X),
	M is X,
	!.

/**
Prototype: elsum(+L1,+L2,-S)
Arguments: L1,L2 and S are lists of same length
Notes:
1. Implement vector addition as list 'addition' of
element by element sums of lists L1 and L2
2. Add corresponding elements recursively
3. DO NOT NEED LIST LENGTH.
*/

elsum([],[],[]):-
	!.

elsum([HL1|TL1], [HL2|TL2], [HS|TS]) :-
	HS is HL1 + HL2,
	elsum(TL1,TL2,TS).
	
/**
Prototype: scaleList(+List,+Scale,-Answer)
Arguments: List, Scale factor, Answer is List with each element divided by scale factor.
Notes:
1. Simple utility for use in forming next set of means.
2. Must handle empty lists and division by zero (see examples).
*/

scaleList([], _, []) :-
	!.

scaleList(A, 0, A).

scaleList([HL|TL], Scale, [HA|TA]) :-
	HA is HL / Scale,
	scaleList(TL, Scale, TA).
	
/**
Prototype: zeroes(+Size,-TheList)
Notes:
creates a list of zeroes (0.0) of length Size (see example)
*/

zeroes(0, []):-
	!.

zeroes(Size, [0.0|TA]) :-
	N is Size - 1,
	zeroes(N, TA),
	!.
	
	
/**
Prototype: zeroMeansSet(+Cmeans,+Dim,-Set)
Note creates a list (Set) of Cmeans lists, each all zeros with length Dim
*/

zeroMeansSet(0, _, []):-
	!.

zeroMeansSet(Cmeans, Dim, [HA|TA]) :-
	C is Cmeans - 1,
	zeroes(Dim, HA),
	zeroMeansSet(C, Dim, TA).
	
	
/**
Prototype: zeroVdiff(+V1,+V2)
Succeeds if V1 and V2 are identical.
*/

zeroVdiff([],[]):-
	!.

zeroVdiff([HA|TA],[HA|TB]):-
	zeroVdiff(TA, TB).
	
/**
Prototype: zeroSetDiff(+S1,+S2)
Arguments: 2 list-of-lists S1 and S2
Succeeds (true) if S1 and S2 are equal; false otherwise
*/

zeroSetDiff([],[]):-
	!.

zeroSetDiff([HA|TA], [HB|TB]):-
	zeroVdiff(HA, HB),
	zeroSetDiff(TA, TB).
	
/**
Prototype: zeroCounts(+C,-CountsList)
like predicate zeroes, but creates integer values
*/

zeroCounts(0, []):-
	!.

zeroCounts(C, [0|TA]):-
	Size is C - 1,
	zeroCounts(Size, TA).
	
/**
Prototype: updateCounts(+P,+Counts,-Updated)
Arguments: Updated Counts list with element P incremented by 1
Notes:
Predicate to keep track of # of elements in a cluster.
Records # of vectors closest to mean P as an integer.
For eventual use in computing new cluster mean.
Easy to see from examples.
Reminder: 0-based indexing
*/

updateCounts(_, [], []):-
	!.

updateCounts(0, [HA|TA], [HB|TB]):-
	HB is HA + 1,
	updateCounts(-1, TA, TB),
	!.

updateCounts(P, [HA|TA], [HB|TB]):-
	S is P - 1,
	HB is HA,
	updateCounts(S, TA, TB).
	
/**
Prototype: updateMeansSum(+V,+X,+Means,-NewMeansSum)
Add a vector, V, to a vector at index X in a set of vectors (Means)
with the result in NewMeansSum.
Notes: It is not necessary to explicitly form the new cluster sets prior to forming
the new means. In fact, this is inefficient.
Instead, we simply keep a running sum of the vectors summed in a cluster
and the number of vectors in the cluster. This predicate is a key part of cmeans computation.
*/

updateMeansSum(_, _, [], []):-
	!.

updateMeansSum(V, 0, [HA|TA], [HB|TB]):-
	elsum(V, HA, HB),
	updateMeansSum(V, -1, TA, TB),
	!.

updateMeansSum(V, X, [HA|TA], [HB|TB]):-
	S is (X - 1),
	HB = HA,
	updateMeansSum(V, S, TA, TB),
	!.
	
/**
Prototype: formNewMeans(+Newmeanssum, +Newcounts,-NewMeans)
Recomputation of the means; this predicate uses Newmeanssum and Newcounts to form NewMeans.
Really just normalization of Newmeanssum for each class by dividing each vector
by the number of vectors in the cluster.
Now you should see the utility of the counting and summing as we classify.
*/

formNewMeans([], [], []):-
	!.

formNewMeans([HNMS|TNMS], [HC|TC], [HNM|TNM]):-
	scaleList(HNMS, HC, HNM),
	formNewMeans(TNMS, TC, TNM).
	
	
/**
Prototype: reclassify(+H, +Currmeans, -UpdatedMeans)
Arguments:
1. H (used recursively, processing a single vector starting at head)
2. Currmeans is current set of c means (required, of course, to allow reclassification of H)
3. UpdatedMeans is 'new' means set.
Notes:
0. The strategy is for this predicate to recursively reclassify each element of H.
(using previously developed functions updateCounts and updateMeans).
1. Other previously developed predicates (including formNewMeans) are used.
An auxiliary predicate recommended (see note 3).
2. We can determine c and vector dimension from Currmeans for count initialization.
3. Notice also reclassify does not explicitly have arguments for Newmeanssum, Newcounts which are
necessary to reclassify. These are initialized with zeroes.
*/

reclass([], [HC|TC], Currcounts, Newmeanssum):-
	length([HC|TC], Size),
	length(HC, HSize),
	zeroMeansSet(Size, HSize, Newmeanssum),
	zeroCounts(Size, Currcounts),
	!.

reclass([HH|TH], Currmeans, Currcounts, Newmeanssum):-
	reclass(TH, Currmeans, UpdatedCount, UpdatedSum),
	distanceSqAllMeans(HH, Currmeans, Lowest),
	listMinPos(Lowest, M),
	updateMeansSum(HH, M, UpdatedSum, Newmeanssum),
	updateCounts(M, UpdatedCount, Currcounts),
	!.
	
reclassify([HH|TH], Currmeans, UpdatedMeans):-
	reclass([HH|TH], Currmeans, Currcounts, Newmeanssum),
	formNewMeans(Newmeanssum, Currcounts, UpdatedMeans),
	!.

/**	
Prototype: cmeans(+H,+MuCurrent,-MuFinal)
MuCurrent starts as muzero; c is derivable from muzero
Stops when means not changing.
*/

cmeans(H, MuCurrent, MuFinal):-
	reclassify(H, MuCurrent, UpdatedMeans),
	zeroSetDiff(MuCurrent, UpdatedMeans),
	MuFinal = UpdatedMeans,
	!.
	
cmeans(H, MuCurrent, MuFinal):-
	reclassify(H, MuCurrent, UpdatedMeans),
	cmeans(H, UpdatedMeans, MuFinal),
	!.
	