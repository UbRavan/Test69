<?php
loadModel('Model');
class Cart_Model extends Model {
    public function __construct(){
        parent::__construct();
    }

    public function getUserCartDetails($userId = null){
        try{
            if($userId !== null){
                $sql = "SELECT ";
                $sql .= "product_id, ";
                $sql .= "product_title, ";
                $sql .= "product_cat, ";
                $sql .= "product_price, ";
                $sql .= "cart.qty as cartQty ";
                $sql .= "FROM cart ";
                $sql .= "JOIN user_info ON cart.user_id = user_info.user_id ";
                $sql .= "JOIN products ON cart.p_id = products.product_id ";
                $sql .= "WHERE cart.user_id = '$userId'";

                $result = $this->mysqli_array_result($this->con, $sql); 
                if(count($result) > 0){
                    return [
                        "status" => 200,
                        "data" => $result
                    ];
                }

                return [
                    "status" => 200,
                    "message" => "No Item on cart"
                ];
            }
        }catch(Exception $e){
            return [
                "status" => 500,
                "message" => $e->getMessage()
             ];
        }
        
    }

    public function getCartCounts($userId = null){
        try{
            if($userId !== null){
                $sql = "SELECT count(id) as `count` FROM cart WHERE cart.user_id = '$userId'";

                $result = $this->mysqli_array_result($this->con, $sql); 
                if(count($result) > 0){
                    return $this->returnResult(200, null, $result[0]);
                }

                return $this->returnResult(200, 'No Item on Cart', ['count' => 0]);
            }
        }catch(Exception $e){
            return $this->returnResult(500, $e->getMessage());
        }
    }

    public function addToCart($userId = null, $productId = null, $qty = null){
        try{
            if($userId !== null && $productId !== null){                
                $check_cart_sql = "SELECT id FROM cart WHERE user_id = '$userId' && p_id = '$productId'";
                $result = $this->mysqli_array_result($this->con, $check_cart_sql);
                if(count($result) > 0){
                    // product already added into cart now we have to update
                    if($qty === null){
                        $update_cart_sql = "UPDATE cart SET qty = qty + 1 WHERE user_id = '$userId' AND p_id = '$productId'";
                    }else{
                        $update_cart_sql = "UPDATE cart SET qty = '$qty' WHERE user_id = '$userId' AND p_id = '$productId'";
                    }

                    mysqli_query($this->con, $update_cart_sql);
                    $affected_rows = mysqli_affected_rows($this->con);
                    if($affected_rows > 0){
                        $message = $affected_rows . ' item updated on Cart';
                        return $this->returnResult(201, $message);
                    }else{
                        return $this->returnResult(200, '0 item updated on Cart');
                    }
                }else{
                    // product does not exist in cart so we have to add new item into cart
                    $sql = "INSERT INTO `cart`(`p_id`, `user_id`, `qty`) VALUES ('$productId','$userId','1')";
                    $query = mysqli_query($this->con, $sql);
                    $affected_rows = mysqli_affected_rows($this->con);
                    if($affected_rows > 0){
                        $message = $affected_rows . ' item added to cart';
                        return $this->returnResult(201, $message);
                    }
                }
                return $this->returnResult(200, 'Something went wrong');
            }
        }catch(Exception $e){
            return $this->returnResult(500, $e->getMessage());
        }
    }

    public function updateCart($userId = null, $cart = []){
        try{
            if($userId !== null && count($cart) !== 0){
                $i = 0;
                $results = [];
                foreach($cart as $cartItem){
                    $productId = $cartItem['productId'];
                    $qty = $cartItem['qty'];
                    $results[] = $this->addToCart($userId, $productId, $qty);
                    if($results[$i]['status'] === 201){
                        $i++;
                    }
                }

                if($i === count($cart)){
                    return $this->returnResult(201, 'Cart updated Successfully');
                }else{
                    return $this->returnResult(200 , null, $results);
                }
                
            }
        }catch(Exception $e){
            return $this->returnResult(500, $e->getMessage());
        }
    }

    public function removeItemsFromCart($userId = null, $cart = []){
        try{
            if($userId !== null){
                //DELETE FROM `cart` WHERE user_id = 3 AND (p_id = 2 OR p_id = 4)
                $del_condition = '';
                foreach($cart as $item){
                    $pid = $item['productId'];
                    $del_condition .= 'p_id = ' . $pid . ' OR ';
                }

                $delete_cond = substr($del_condition, 0, -4);
                $delete_from_cart = "DELETE FROM cart WHERE user_id = '$userId' AND ($delete_cond)";
                mysqli_query($this->con, $delete_from_cart);

                if(mysqli_affected_rows($this->con)){
                    return $this->returnResult(200, count($cart) . 'cart Item Removed Successfully');
                }else{
                    return $this->returnResult(500, 'Something went wrong');
                }
            }
        }catch(Exception $e){
            return $this->returnResult(500, $e->getMessage());
        }
    }

   

}