import random
import os

def clear_screen():
    """Clears the console."""
    os.system('cls' if os.name == 'nt' else 'clear')

def print_confetti(num_pieces):
    """Prints a specified number of confetti pieces at random positions."""
    terminal_width, terminal_height = os.get_terminal_size()
    
    for _ in range(num_pieces):
        x = random.randint(0, terminal_width - 1)
        y = random.randint(0, terminal_height - 1)
        print(f"\033[{y};{x}H", end="")
        print(random.choice(['✨', '💥']), end="")
        print("\033[0m", end="")

if __name__ == "__main__":
    num_pieces = 350  # Adjust the number of confetti pieces
    print_confetti(num_pieces)
    input("Press Enter to clear...")
    clear_screen()
