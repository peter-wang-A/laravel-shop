<?php

return [
    'alipay' => [
        'app_id'         => '2021000116681080',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuJJA5NNH0vZsfIks5dCW0DBJ3NWV8a6Hplv1reJRdBPNhPhXXbhnDBjrbmW9SXg3AYBr3HIqV/Fh5aGseCLVSF8HAJXxp+b6nkX/M1Mv2F5KLDbOclkRnZVkpLe+SeC596kIsCML2nbct4YVmc+8Gm+Xa+7Z7QAF8gBLbPPSfMKibGtc6uiOz1wmU6X/7Y5XIjzebMsFCPkad4y0FIPiLSBAa3piQ1dd68dtQw6/wfBvZIHbxZn6ZPoXAzTZOXpus/jtKyWb/S3Oi6UbdtgH0FvHshHs6ioT6/yDCrsDKVqLHQGHtBpzKF8zc0ZQcXmEWDA/IDQn2CwqaXsqSipFBwIDAQAB',
        'private_key'    => 'MIIEowIBAAKCAQEAnSsKWiwMC+mEUPrL0IE94JW6lLGvIA9yD+r9lWLONVJwWA9AqjjeG6TgY6D9qPF64moO0EYoSYFRS6mKReHAuaVt/Wb4Yzz0t4o/LJrZRqfg7rsB164e9Q1b4C1l9bP1KEEVMFxMM5ljjJgyLlpZHN+FJY/MZqd0s3YFk7ELjZHcV+YrvnMI0Ehc+Q1f3A/Nij99wpXOQLn0vs8T04O7aePSDMw6INd66Cm3z2GTXiqNl8gLbkylNkRnB35+VgCKNdSvJvNmliMPBy58dUm+RVh/7Qnn4KfqEh9j2kFQ7L586T9qsSYulZrZ1VpW92Y4y+rS9hCJOOy7ujwQ75aTEQIDAQABAoIBAHbkvVMTaRWF1GOSJaaYbl+7xMaBV9Jv/MBP6v8cHJQ3xebHuwpKNLNC2cBkZB9udaBbzNL/j7BzVDVhjsloiuulWnxMbfBch19lBH9QsLcDfmP6Md2fyallLrB6h1wEg5utCENqPvb1vVn5NFTeYfU2nbmZe23Yla7AxF1CnFO95o7PbYW+otRfFCT4l2GoPPd/WlAjEbqV66YoIlA4oGeMbNL1cMQaC+n+cfj2b5CVaoXqP2/B4zY8QQyBlLJ0cDP4fxfwvdnviUqDAdCjZ5uORTmXsS/YUnd7zdzLwl7a+yhKj3Efhapv+UnaJCdXB0mlCWhZzoXHpv1yUcNXSAECgYEA5MbdSTSlKb8FzoCX1KHng2vkQXV+eGcTylXhEfYjnquWJsAys9E0y96bRaqfNVmpzYsxi3dKs/3b+LE2xL4xWpah/B5LZX5IdWS3+djSsUWrjaehB7kxeukbon9nJsQzQ7qtxJM2jCd11asCXbah5s+qf72yztQ2NwAnTaIrLpECgYEAr97KH6+OGM1zcZGzW0P9sjw9/UqUYo9PL/XfpQe6cUrqpKrEubfJuJr9YKL3ybthTYRVvYLEJgtQ9JbC0i1YATlbMqL0A8j9+wQsfgRwmvA0r27f3mKpYffO+wuvPkcwQTHNiJURUCmies/yKpmPh1aWmDCEJtffX+4RRr2YXIECgYATIS3PuT82RmrN4aLQfNlG+/aSIqj4mGXnPVcckWBZHC/p/Zg/wPaRNYP2R1YYt+6i6UizSL5OXqPOH8NcoCqwUHgrBL15Nx4H8uwPUDoN6K13otSsOKgShvtwkwalDzLaLvnovgaJoaMQwsPn4iWXjzXKiKoy8Tu3TaP7sdu6gQKBgQCGzISv9Kc9a3vouHx4n4x8aKA63EcPpDhV0mcw3tOQspNW020lzDDZRjKfN0olXXIfMCdnsisV3eaXElcRMaAakBPOpAfUPuIs2+4eSNjmaOZ48Mq2cXjSllYXJcWUPoxF7B5VaaxzfPYEnA5JiJnyafPTgpxICX400Qx85CU8AQKBgBoYxzmgeZcY/hosmwsjzi3ICCJae5mG6gePQrTCOULD43niyo0HHbFAELH2ewS3ibc2TRvf/gi9V5fBquz1lLiHtaKKC4BOReC+nBSJkFreImpoPKfxkq3DWkz+Li3cywKfBHqbdfEbukQ4zN2LNop3FfdnkEbIq+NH6zK7Vk4I',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];
