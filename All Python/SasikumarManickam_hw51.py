# Written by Sasikumar Manickam
# Date  10/17/2017
# Assignment: Week 05.1
# Print Quads using inner loops


def squareQuads(n):	
    if (n%2 ==1):
        midPoint = n//2
    else:
        print('squareQuads parmaeter must be must be a even number')
        return
       
    for i in range(n):
        for j in range(n):
            # decide when to print '*'
            if (j==0 or i==0  or j==n-1 or i==n-1 or  (midPoint == j and midPoint> 0) or (midPoint == i and midPoint> 0)):
                print('*', end=' ')
            else:
                print(' ', end=' ')
        print()
    return

def squareDiagonals(n):
     for i in range(n):
        for j in range(n):
            # decide when to print '*'
            if (j==0 or i==0  or j==n-1 or i==n-1 or i==j  or j == (n-i-1)):
                print('*', end=' ')
            else:
                print(' ', end=' ')
        print()
  #return
  
  

def squareTriangle(n):
    for i in range(n):
        for j in range(n):
            # decide when to print '*'
            if (j==0 or i==0  or j==n-1 or i==n-1):
                print('*', end=' ')
            elif (n > 1 and i > 1 and j > 1 and i < n-j):
                print('*', end=' ')
            else:
                print(' ', end=' ')
        print()

##  Test

##  >>> squareQuads(11)
##  * * * * * * * * * * * 
##  *         *         * 
##  *         *         * 
##  *         *         * 
##  *         *         * 
##  * * * * * * * * * * * 
##  *         *         * 
##  *         *         * 
##  *         *         * 
##  *         *         * 
##  * * * * * * * * * * * 
##  >>> squareDiagonals(11)
##  * * * * * * * * * * * 
##  * *               * * 
##  *   *           *   * 
##  *     *       *     * 
##  *       *   *       * 
##  *         *         * 
##  *       *   *       * 
##  *     *       *     * 
##  *   *           *   * 
##  * *               * * 
##  * * * * * * * * * * * 
##  >>> squareTriangle(11)
##  * * * * * * * * * * * 
##  *                   * 
##  *   * * * * * * *   * 
##  *   * * * * * *     * 
##  *   * * * * *       * 
##  *   * * * *         * 
##  *   * * *           * 
##  *   * *             * 
##  *   *               * 
##  *                   * 
##  * * * * * * * * * * * 
