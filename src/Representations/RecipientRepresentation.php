<?php


namespace NotificationChannels\ExpoPushNotifications\Representations;


class RecipientRepresentation
{

    protected $type;
    protected $id;
    protected $token;
    protected $device_id;

    public function __construct()
    {

    }

    public static function create()
    {
        return new static();
    }

    public function type(string $value)
    {
        $this->type = $value;

        return $this;
    }

    public function id(string $value)
    {
        $this->id = $value;

        return $this;
    }

    public function token(string $value)
    {
        $this->token = $value;

        return $this;
    }

    public function deviceId(string $value)
    {
        $this->device_id = $value;

        return $this;
    }

    public function toArray()
    {
        $data = [];

        if (!is_null($this->type)) {
            $data['type'] = $this->type;
        }

        if (!is_null($this->id)) {
            $data['id'] = $this->id;
        }

        if (!is_null($this->token)) {
            $data['token'] = $this->token;
        }

        if (!is_null($this->device_id)) {
            $data['device_id'] = $this->device_id;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getDeviceId(): ?string
    {
        return $this->device_id;
    }

}
