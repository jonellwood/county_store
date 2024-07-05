<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 07/05/2024
Purpose: Shopping Cart Class 
Includes:   none

*/
// start session 
if (!session_id()) {
    session_start();
}
class Cart
{
    protected $cart_contents = array();

    /**
     * Initializes the cart by retrieving the cart contents from the session.
     *
     * @return void
     */
    public function __construct()
    {
        // get cart contents from session
        $this->cart_contents = !empty($_SESSION['cart_contents']) ? $_SESSION['cart_contents'] : array('cart_total' => 0, 'total_items' => 0, 'total_logo_fees' => 0);
    }

    /**
     * A description of the entire PHP function.
     *
     * @return array
     */
    public function contents(): array
    {
        return array_reverse($this->cart_contents, true); // Maintain key association
    }

    /**
     * Retrieves the item from the cart with the specified row ID.
     *
     * @param string $row_id The ID of the item to retrieve.
     * @return mixed The item if found, false otherwise.
     */
    public function get_item(string $row_id): mixed
    {
        return array_key_exists($row_id, $this->cart_contents) ? $this->cart_contents[$row_id] : false;
    }

    /**
     * Retrieves the total number of items in the cart.
     *
     * @return int The total number of items in the cart.
     */
    public function total_items(): int
    {
        return $this->cart_contents['total_items'];
    }

    /**
     * Retrieves the total value of the cart.
     *
     * @return float The total value of the cart.
     */
    public function total(): float
    {
        return $this->cart_contents['cart_total'];
    }
    /**
     * Retrieves the total logos fees of the cart.
     * 
     * @return float The total logos fees of the cart.
     */
    public function total_logo_fees(): float
    {
        return $this->cart_contents['total_logo_fees'];
    }


    /**
     * Inserts an item into the cart.
     *
     * @param array $item The item to be inserted. It should have the following keys:
     *                    - id: The ID of the item.
     *                    - name: The name of the item.
     *                    - price: The price of the item.
     *                    - qty: The quantity of the item.
     *                    - add_item_uid: The unique identifier for the item.
     * @return bool Returns true if the item is successfully inserted into the cart, false otherwise.
     */
    public function insert(array $item): bool
    {
        if (empty($item) || !array_key_exists('id', $item) || !array_key_exists('name', $item) || !array_key_exists('price', $item) || !is_numeric($item['price']) || !array_key_exists('qty', $item) || !is_numeric($item['qty']) || $item['qty'] <= 0 || !array_key_exists('add_item_uid', $item)) {
            return false;
        }
        $item['qty'] = (float) $item['qty'];
        $row_id = $item['add_item_uid'];
        $old_qty = isset($this->cart_contents[$row_id]['qty']) ? (int) $this->cart_contents[$row_id]['qty'] : 0;
        $item['qty'] += $old_qty;
        $item['rowid'] = $row_id;
        $this->cart_contents[$row_id] = $item;

        return $this->save_cart();
    }

    /**
     * Updates the cart with the provided item data.
     *
     * @param array $item The item data to update the cart with.
     * @return bool Returns true if the cart is successfully updated, false otherwise.
     */
    public function update(array $item): bool
    {
        if (empty($item) || !array_key_exists('rowid', $item) || !array_key_exists($item['rowid'], $this->cart_contents)) {
            return false;
        }

        $updateKeys = array_intersect(array_keys($this->cart_contents[$item['rowid']]), array_keys($item));

        $item['qty'] = isset($item['qty']) ? (float) $item['qty'] : null;

        if (isset($item['qty']) && $item['qty'] <= 0) {
            unset($this->cart_contents[$item['rowid']]);
            return true;
        }

        foreach ($updateKeys as $key) {
            if (in_array($key, ['price', 'logoFee'])) {
                $item[$key] = (float) $item[$key];
            }
            $this->cart_contents[$item['rowid']][$key] = $item[$key];
        }

        $this->save_cart();
        return true;
    }

    /**
     * Saves the cart contents to the session.
     *
     * This function iterates over the cart contents and calculates the total number of items and the total cart value.
     * It also calculates the subtotal, logo fee subtotal, and subtotal with logo fee for each item.
     * The updated cart contents are then saved to the session.
     *
     * @return bool Returns true if the cart contents were successfully saved, false otherwise.
     */
    protected function save_cart(): bool
    {
        $totalItems = $cartTotal = 0;
        $totalLogoFees = 0;
        foreach ($this->cart_contents as $key => $item) {
            if (!is_array($item) || !array_key_exists('price', $item) || !array_key_exists('qty', $item)) {
                continue;
            }

            $totalItems += $item['qty'];
            $cartTotal += $item['price'] * $item['qty'];
            $logoFee = isset($item['logoFee']) ? $item['logoFee'] * $item['qty'] : 0;
            $totalLogoFees += $logoFee;

            $item['subtotal'] = $item['price'] * $item['qty'];
            $item['logoFeeSubtotal'] = isset($item['logoFee']) ? $item['logoFee'] * $item['qty'] : 0;
            $item['subtotalWithLogoFee'] = $item['subtotal'] + $item['logoFeeSubtotal'];

            $this->cart_contents[$key] = $item;
        }

        $this->cart_contents['total_items'] = $totalItems;
        $this->cart_contents['cart_total'] = $cartTotal;
        $this->cart_contents['total_logo_fees'] = $totalLogoFees;

        if (count($this->cart_contents) <= 2) {
            unset($_SESSION['cart_contents']);
            return false;
        }

        $_SESSION['cart_contents'] = $this->cart_contents;
        return true;
    }
    /**
     * Removes an item from the cart based on its row ID.
     *
     * @param string $row_id The row ID of the item to remove.
     * @throws InvalidArgumentException If the row ID is not a string.
     * @return bool Returns true if the item was successfully removed, false if it was not found.
     */
    public function remove(string $row_id): bool
    {
        if (!is_string($row_id)) {
            throw new InvalidArgumentException('Invalid row ID. Must be a string.');
        }

        if (!array_key_exists($row_id, $this->cart_contents)) {
            // Item not found, but technically not an error
            return false;
        }

        unset($this->cart_contents[$row_id]);
        $this->save_cart();
        return true;
    }
    /**
     * Destroys the cart by resetting the cart contents to an empty array and unsetting the cart contents from the session.
     *
     * @return void
     */
    public function destroy(): void
    {
        $this->cart_contents = array('cart_total' => 0, 'total_items' => 0);
        unset($_SESSION['cart_contents']);
    }
    /**
     * Serializes the cart contents into a JSON string.
     *
     * @return string The JSON representation of the cart contents.
     */
    public function serializeCart(): string
    {
        return json_encode($this->cart_contents);
    }
}
