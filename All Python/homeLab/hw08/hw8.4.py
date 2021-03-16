# Written by Sasikumar Manickam
# Date  11/07/2017
# Assignment: Week 08.2
# Recursions - Password recursion

def prompt():
    passw = input("Enter Password:")
    if len(passw) !=0:
        return passw
    else:
        prompt()