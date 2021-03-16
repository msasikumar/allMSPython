import os
import glob
import time
#defining the RPi's pins as Input / Output
import RPi.GPIO as GPIO
from datetime import datetime

base_dir = '/sys/bus/w1/devices/'
device_folder = glob.glob(base_dir + '28*')[0]
device_file = device_folder + '/w1_slave'

def read_temp_raw():
    f = open(device_file, 'r')
    lines = f.readlines()
    f.close()
    return lines

#FAHRENHEIT CALCULATION
def read_temp_f():
    lines = read_temp_raw()
    while lines[0].strip()[-3:] != 'YES':
        time.sleep(0.5)
        lines = read_temp_raw()
    equals_pos = lines[1].find('t=')
    if equals_pos != -1:
        temp_string = lines[1][equals_pos+2:]
        temp_f = (int(temp_string) / 1000.0) * 9.0 / 5.0 + 32.0 # TEMP_STRING IS THE SENSOR OUTPUT, MAKE SURE IT'S AN INTEGER TO DO THE MATH
        temp_f = str(round(temp_f, 1)) # ROUND THE RESULT TO 1 PLACE AFTER THE DECIMAL, THEN CONVERT IT TO A STRING
        return temp_f

#used for GPIO numbering
GPIO.setmode(GPIO.BCM)

#closing the warnings when you are compiling the code
GPIO.setwarnings(False)

RUNNING = True

#defining the pins
blue = 23
red = 17
green= 24

#defining the pins as output
GPIO.setup(red, GPIO.OUT)
GPIO.setup(green, GPIO.OUT)
GPIO.setup(blue, GPIO.OUT)

#choosing a frequency for pwm
Freq = 100

#defining the pins that are going to be used with PWM
RED = GPIO.PWM(red, Freq)
GREEN = GPIO.PWM(green, Freq)
BLUE = GPIO.PWM(blue, Freq)


far = read_temp_f()
cel = read_temp_c()
hold = far 
GREEN.start(100)
printf = True
RUNNING = True
stat = 'STABLE'

try:
    while RUNNING == True:

        print(datetime.now().strftime("[%m/%d/%Y, %H:%M:%S]:") + "      Temprature:" + far +"F.  and it is " + stat)
        if (far > hold):
           stat = 'HEATING!'
           RED.start(100)
           BLUE.start(1)
           GREEN.start(1)
           hold = far
        elif (far < hold):
           stat= 'COOLING!'
           RED.start(1)
           BLUE.start(100)
           GREEN.start(1)
           hold = far
        elif (far == hold):
           stat = 'STABLE.'
           RED.start(1)
           BLUE.start(1)
           GREEN.start(100)
           
        far = read_temp_f()
        cel = read_temp_c()
except KeyboardInterrupt:
    # the purpose of this part is, when you interrupt the code, it will stop the while loop and turn off the pins, which means your LED won't light anymore
	RUNNING = False
    print ('Bye!')
    GPIO.cleanup()