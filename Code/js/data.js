
const PRODUCTS = [
    {
        id: 1,
        name: 'Шлем AGV K6 S',
        brand: 'AGV',
        category: 'Шлемы',
        price: 45900,
        hot: true,
        tags: ['DOT', 'ECE'],
        image: 'assets/products/1.jpg'
    },
    {
        id: 2,
        name: 'Куртка Alpinestars T-GP Plus',
        brand: 'Alpinestars',
        category: 'Куртки',
        price: 28900,
        hot: true,
        tags: ['лето', 'сетка'],
        image: 'assets/products/icon.jpg'
    },
    {
        id: 3,
        name: 'Перчатки Dainese Carbon',
        brand: 'Dainese',
        category: 'Перчатки',
        price: 8900,
        hot: true,
        tags: ['карбон', 'спорт'],
        image: 'assets/products/gloves1.jpg'
    },
    {
        id: 4,
        name: 'Ботинки TCX SP-Master',
        brand: 'TCX',
        category: 'Обувь',
        price: 19500,
        hot: false,
        tags: ['кожа', 'защита'],
        image: 'assets/products/boots1.jpg'
    },
    {
        id: 5,
        name: 'Штаны Revit Ignition 3',
        brand: 'Revit',
        category: 'Штаны',
        price: 22400,
        hot: false,
        tags: ['текстиль', 'все сезоны'],
        image: 'assets/products/pants1.jpg'
    },
    {
        id: 6,
        name: 'Шлем Shoei NXR2',
        brand: 'Shoei',
        category: 'Шлемы',
        price: 52800,
        hot: true,
        tags: ['премиум', 'тихий'],
        image: 'assets/products/helmet2.jpg'
    },
    {
        id: 7,
        name: 'Куртка Spidi Warrior',
        brand: 'Spidi',
        category: 'Куртки',
        price: 35600,
        hot: false,
        tags: ['кожа', 'защита'],
        image: 'assets/products/jacket2.jpg'
    },
    {
        id: 8,
        name: 'Перчатки Held Air n Dry',
        brand: 'Held',
        category: 'Перчатки',
        price: 7200,
        hot: false,
        tags: ['дождь', 'лето'],
        image: 'assets/products/gloves2.jpg'
    },
    {
        id: 9,
        name: 'Ботинки Sidi Gavia Gore',
        brand: 'Sidi',
        category: 'Обувь',
        price: 24100,
        hot: false,
        tags: ['Gore-Tex', 'тур'],
        image: 'assets/products/boots2.jpeg'
    },
    {
        id: 10,
        name: 'Штаны Furygan AFS 18',
        brand: 'Furygan',
        category: 'Штаны',
        price: 16800,
        hot: false,
        tags: ['джинсы', 'кэжуал'],
        image: 'assets/products/pants2.jpg'
    },
    {
        id: 11,
        name: 'Шлем HJC RPHA 11',
        brand: 'HJC',
        category: 'Шлемы',
        price: 38200,
        hot: false,
        tags: ['трек', 'лёгкий'],
        image: 'assets/products/shoei2.jpg'
    },
    {
        id: 12,
        name: 'Куртка Rukka Nivala',
        brand: 'Rukka',
        category: 'Куртки',
        price: 41500,
        hot: false,
        tags: ['зима', 'Gore-Tex'],
        image: 'assets/products/icon2.jpg'
    }
];


const DEFAULT_USERS = [
    {
        id: 1,
        name: 'Алекс Райдер',
        email: 'rider@motosoul.ru',
        password: 'moto123',
        age: 28,
        role: 'Мотоциклист'
    },
    {
        id: 2,
        name: 'Мария Скорость',
        email: 'maria@motosoul.ru',
        password: 'moto123',
        age: 24,
        role: 'Турист'
    },
    {
        id: 3,
        name: 'Дмитрий Трек',
        email: 'dmitry@motosoul.ru',
        password: 'moto123',
        age: 32,
        role: 'Спортсмен'
    }
];

const DEFAULT_THREADS = [
    {
        id: 1,
        cat: 'Маршруты',
        title: 'Кольцо вокруг Байкала за 5 дней — маршрут и заправки',
        author: 'Алекс Райдер',
        date: '2 дня назад',
        replies: 14,
        views: 328
    },
    {
        id: 2,
        cat: 'Техника',
        title: 'Обслуживание цепи после дождя: что использовать?',
        author: 'Дмитрий Трек',
        date: '5 дней назад',
        replies: 8,
        views: 156
    },
    {
        id: 3,
        cat: 'Экипировка',
        title: 'Сравнение AGV K6 и Shoei NXR2 — ваш опыт?',
        author: 'Мария Скорость',
        date: '1 неделю назад',
        replies: 22,
        views: 512
    },
    {
        id: 4,
        cat: 'Мероприятия',
        title: 'MotoSoul Ride Out — встреча 15 июня',
        author: 'Алекс Райдер',
        date: '3 дня назад',
        replies: 31,
        views: 890
    }
];
