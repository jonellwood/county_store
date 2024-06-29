import time
import sys
import random
import os

def makeDots(dots_string,initial_delay, additional_delay):
    for char in dots_string:
        sys.stdout.write(char)
        sys.stdout.flush()
        time.sleep(0.2)
        time.sleep(0.05)
    print()

def print_confetti(num_pieces):
    terminal_width, terminal_height = os.get_terminal_size()
    
    for _ in range(num_pieces):
        x = random.randint(0, terminal_width - 1)
        y = random.randint(0, terminal_height - 1)
        print(f"\033[{y};{x}H", end="")
        print(random.choice(['✨', '💥']), end="")
        print("\033[0m", end="")


print("Receiving request ✉️ ")

makeDots("....... ✅", 0.2, 0.4)


print("endpoint identified... \"employeeData.employees\" 🎯")
makeDots("..... ✅ ", 0.2, 0.35)


print("🔎 retrieving available query fields.")
makeDots("...... ✅", 0.2, 0.65)

print("requested fields... \"sFirstName\", \"sLastName\", \"sDateOfBirth\" . 📋")
makeDots(".......... ✅", 0.2, 0.5)

print("🧙 mutating request fields")
makeDots("............ ✅", 0.2, 0.75)

print("sFirstName => f_name 🧨")
makeDots("... ✅", 0.2, 0.25)

print("fLastName => l_name 🧨")
makeDots(".... ✅", 0.2, 0.3)

print("dDateOfBirth => dob 💥")
makeDots("....... ✅", 0.2, 0.5)

print("🚀 sending query 'SELECT f_name as sFirstName, l_name as sLastName, dob as dDateOfBirth from employees;'  🚀")
makeDots(".w.a.i.t.i.n.g.", 0.2, 0.4)
makeDots("........... ✅", 0.25, 0.7)

print ("ℹ️  Data received - sending back to requestor ")
makeDots("....... ✅", 0.2, 0.4)
print("{\n")
time.sleep(0.45)
print("    \"sFirstName\": \"John\",\n")
time.sleep(0.45)
print("    \"sLastName\": \"Doe\",\n")
time.sleep(0.45)
print("    \"dDateOfBirth\": \"04-20-1969\"\n")
time.sleep(0.45)
print("}")
time.sleep(0.75)
print(' 🎊 Request completed 🎉') 
makeDots("..... ✅", 0.1, 0.25) 
print("Jon is so smart!  😏")
# time.sleep(2)
# print_confetti(350)
# print("............")
# print("............")