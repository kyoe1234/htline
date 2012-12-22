<?php
/**
 * @brief 이벤트 발생과 비슷한 용도의 클래스
 */
class Action {
    private static $callback_list = array();
    private static $exec_name_list = array();

    /**
     * @brief 함수 또는 메소드의 존재 확인
     * @param $function mixed 함수
     */
    private static function check_function($function) {
        if ( is_array($function) ) {
            if ( !method_exists($function[0], $function[1]) ) {
                trigger_error("{$function[0]}::{$function[1]} 메소드를 찾을 수 없습니다.", E_USER_ERROR);
            }
        } else {
            if ( !function_exists($function) ) {
                trigger_error("{$function} 함수를 찾을 수 없습니다.", E_USER_ERROR);
            }
        }
    }

    /**
     * @brief 전체 콜백 목록을 반환한다.
     * @return array
     */
    public static function callback_list() {
        return self::$callback_list;
    }

    /**
     * @brief 액션이름별 액션 실행시 호출될 콜백함수와 콜백인수 추가
     * @details 콜백함수와 콜백인수는 call_user_func_array()의 인수 형식과 같다.
     * @param $name string 액션이름
     * @param $callback_function mixed 콜백함수
     * @param $callback_param array 콜백인수
     */
    public static function add($name, $callback_function, $callback_param = null) {
        self::check_function($callback_function);
        self::$callback_list[$name][] = array($callback_function, $callback_param);
    }

    /**
     * @brief 액션이름에 추가된 콜백함수 실행
     * @details add()로 추가된 콜백함수가 실행되며 함수로 전달되는 인수는 아래와 같다.\n
     * @code
     * function callback_foo($action) {
     *     // $action : 액션 정보를 담은 객체
     *     // $action->name : 액션이름
     *     // $action->target : 액션을 실행한 호출자
     *     // $action->param : 액션 호출자가 전달하는 인수
     *     // $action->callback_param : add()에 사용한 콜백인수
     * }
     * @endcode
     * @param $name string 액션이름
     * @param $param mixed 콜백함수에 전달하고픈 인수
     * @return boolean
     */
    public static function exec($name, $param = null) {
        if ( !is_array(self::$callback_list[$name]) ) return true;

        // 현재 실행중인 이름에 추가
        self::$exec_name_list[] = $name;

        // 함수 호출자
        list(, $caller) = debug_backtrace();

        if ( $caller['type'] == '->' ) {
            $target = $caller['object'];
        } else if ( $caller['type'] == '::' ) {
            $target = $caller['class'];
        } else {
            $target = $caller['function'];
        }

        foreach ( self::$callback_list[$name] as $callback ) {
            list($callback_function, $callback_param) = $callback;

            self::check_function($callback_function);

            $action = new self($name, $target, $param, $callback_param);

            $result = call_user_func_array($callback_function, array($action, $param));
            if ( $result === false ) {
                break; // 반환값이 false면 더이상 진행하지 않음
            }
        }

        // 현재 실행중인 이름에서 꺼냄
        array_pop(self::$exec_name_list);

        return $result !== false;
    }

    public $name; /**< @brief 액션이름 */
    public $target; /**< @brief 액션을 실행한 호출자 */
    public $param; /**< @brief 액션 호출자가 전달하는 인수 */
    public $callback_param; /**< @brief 콜백 인수 */

    /**
     * @brief 생성자
     * @param $name string 액션이름
     * @param $target mixed 액션을 실행한 호출자
     * @param $param mixed 액션 호출자가 전달하는 인수
     * @param $callback_param mixed add()에 사용한 콜백인수
     */
    private function __construct($name, $target, $param, $callback_param) {
        $this->name = $name;
        $this->target = $target;
        $this->param = $param;
        $this->callback_param = $callback_param;
    }
}
?>