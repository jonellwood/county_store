<?php
// start session 
if (!session_id()) {
    session_start();
}

class Cart
{
    // protected $cart_contents variable holds the items in the User's cart 
    protected $cart_contents = array();


    public function __construct()
    {
        // get cart contents from session 
        $this->cart_contents = !empty($_SESSION['cart_contents']) ? $_SESSION['cart_contents'] : NULL;
        if ($this->cart_contents === NULL) {
            // set some base values 
            $this->cart_contents = array('cart_total' => 0, 'total_items' => 0);
        }
    }

    // returns the entire cart array
    public function contents()
    {
        // arrange by newest items first
        $cart = array_reverse($this->cart_contents);

        // remove these values becuase they create a problem when showing the cart table
        unset($cart['total_items']);
        unset($cart['cart_total']);

        return $cart;
    }

    // get specific cart items details
    public function get_item($row_id)
    {
        return (in_array($row_id, array('total_items', 'cart_total'), TRUE) or !isset($this->cart_contents[$row_id]))
            ? FALSE
            : $this->cart_contents[$row_id];
    }

    // return the total items count
    public function total_items()
    {
        return $this->cart_contents['total_items'];
    }

    // returns the total price
    public function total()
    {
        return $this->cart_contents['cart_total'];
    }

    // insert items into the cart and save to the session

    public function insert($item = array())
    {
        if (!is_array($item) or count($item) === 0) {
            return FALSE;
        } else {
            if (!isset($item['id'], $item['name'], $item['price'], $item['qty'], $item['add_item_uid'])) {
                return FALSE;
            } else {
                // insert item

                // prep qty 
                $item['qty'] = (float) $item['qty'];
                if ($item['qty'] === 0) {
                    return FALSE;
                }
                // prep price
                $item['price'] = (float) $item['price'];
                // create UID for item being inserted

                $rowid = ($item['add_item_uid']);

                // get qty if its already there and add it up
                $old_qty = isset($this->cart_contents[$rowid]['qty']) ? (int) $this->cart_contents[$rowid]['qty'] : 0;
                // recreate entry with uid and updated qty
                $item['rowid'] = $rowid;
                $item['qty'] += $old_qty;
                $this->cart_contents[$rowid] = $item;

                // save Cart item
                if ($this->save_cart()) {
                    return isset($rowid) ? $rowid : TRUE;
                } else {
                    return FALSE;
                }
            }
        }
    }
    // update cart
    public function update($item = array())
    {
        if (!is_array($item) or count($item) === 0) {
            return FALSE;
        } else {
            if (!isset($item['rowid'], $this->cart_contents[$item['rowid']])) {
                return FALSE;
            } else {
                //prep the qty
                if (isset($item['qty'])) {
                    $item['qty'] = (float) $item['qty'];
                    //remove item from cart if qty is zero
                    if ($item['qty'] === 0) {
                        unset($this->cart_contents[$item['rowid']]);
                        return TRUE;
                    }
                }

                // find updatable keys
                $keys = array_intersect(array_keys($this->cart_contents[$item['rowid']]), array_keys($item));
                // prep the price
                if (isset($item['price'])) {
                    $item['price'] = (float) $item['price'];
                }
                // product id and name should stay the same
                foreach (array_diff($keys, array('id', 'name')) as $key) {
                    $this->cart_contents[$item['rowid']][$key] = $item[$key];
                }
                // save cart data
                $this->save_cart();
                return TRUE;
            }
        }
    }



    // save cart array to session
    protected function save_cart()
    {
        $this->cart_contents['total_items'] = $this->cart_contents['cart_total'] = 0;
        foreach ($this->cart_contents as $key => $val) {
            // make sure array contains proper indexes
            if (!is_array($val) or !isset($val['price'], $val['qty'])) {
                continue;
            }
            $this->cart_contents['cart_total'] += ($val['price'] * $val['qty']);
            $this->cart_contents['total_items'] += $val['qty'];
            $this->cart_contents[$key]['subtotal'] = ($this->cart_contents[$key]['price'] * $this->cart_contents[$key]['qty']);
        }
        // if cart is empty delete if from the session
        if (count($this->cart_contents) <= 2) {
            unset($_SESSION['cart_contents']);
            return FALSE;
        } else {
            $_SESSION['cart_contents'] = $this->cart_contents;
            return TRUE;
        }
    }

    // removes an item from the cart
    public function remove($row_id)
    {
        // unset and save
        unset($this->cart_contents[$row_id]);
        // or should it be $rowid ?
        $this->save_cart();
        return TRUE;
    }

    // empties cart and destroys the session
    public function destroy()
    {
        $this->cart_contents = array('cart_total' => 0, 'total_items' => 0);
        unset($_SESSION['cart_contents']);
    }
}
