<?php
namespace Api\Routers;


abstract class Router
{
 
    /**
     * Зарезервировано для специальных роутингов, в классах роутинга не используется.
     * @see index.php
     * 
     */
    const ROUTER_SPECIAL = 1;
    /**
     * Роутинг с REST
     * 
     */
    const ROUTER_REST    = 2;
    
    /**
     * Реализует логики абстрактной фабрики. 
     * Создает подходящий объект для роутинга.
     * 
     * @param  int    $type - тип роутинга ( константы self::TYPE_ )
     * @return Router - объект реализующий выбранный роутинг
     */
    public static function getInstance($type)
    {
        switch ( $type )
        {
            case self::ROUTER_REST:
            {
                require_once __DIR__ . '/REST.php';
                return new REST;
            }
        }
    }
    
    /**
     * Конструктор не должен выбрасывать исключения AppException, вместо
     * этого, после разбора роутинга ошибка, если таковая была, должна
     * быть возвращена через метод @see self::getError()
     * 
     * Такое поведение требуется для того, чтобы роутер всегда мог определять
     * формат данных @see self::getFormat()
     * 
     */
    public abstract function __construct();
    
    /**
     * Возвращает имя контроллера
     * 
     * @return string
     */
    public abstract function getController();
    
    /**
     * Возвращает CRUD метод ( константы self::CRUD_* )
     * 
     * @return int
     */
    public abstract function getMethod();
    
    /**
     * Вовзращает параметры указанные в роутинге. Например,
     * при роутинге - /user/:group/:id и полученом url - /user/admins/5 
     * метод вернет массив. array('group' => 'admins', 'id' => '5').
     * Если параметров нет, то должен вернуть пустой массив.
     * 
     * @return array
     */
    public abstract function getParams();
    
    /**
     *  Возвращает версию API ( константы self::VER_* )
     * 
     * @return int
     */
    public abstract function getVersion();
    
    /**
     * Формат возвращаемых данных ( константы self::FORMAT_* ).
     * 
     * Формат обязательно нужно определить, даже если запрос, в целом, был с ошибкой!
     * Если возможности определить формат нет, то нужно использовать какой-то общий
     * формат, чтобы иметь возможность хоть как-то сообщить клиенту об ошибке.
     * 
     * @return int
     */
    public abstract function getFormat();
    
    /**
     * Возвращает 0, если разбор роутинга произошел успешно
     * или номер ошибки из AppException::ROUTER_* в случае неудачи.
     * 
     * @return int
     */
    public abstract function getError();
    
}
