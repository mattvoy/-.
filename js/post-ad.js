const cities = [
  "Москва",
  "Санкт-Петербург",
  "Казань",
  "Новосибирск",
  "Екатеринбург",
  "Нижний Новгород",
  "Челябинск",
  "Самара",
  "Уфа",
  "Ростов-на-Дону",
  "Омск",
  "Красноярск",
  "Воронеж",
  "Пермь",
  "Волгоград",
  "Краснодар",
  "Саратов",
  "Тюмень",
  "Тольятти",
  "Ижевск",
  "Барнаул",
  "Ульяновск",
  "Иркутск",
  "Хабаровск",
  "Ярославль",
  "Владивосток",
  "Махачкала",
  "Томск",
  "Оренбург",
  "Кемерово",
  "Новокузнецк",
  "Рязань",
  "Астрахань",
  "Набережные Челны",
  "Пенза",
  "Липецк",
  "Киров",
  "Тула",
  "Чебоксары",
  "Калининград",
  "Курск",
  "Ставрополь",
  "Сочи",
  "Улан-Удэ",
  "Тверь",
  "Магнитогорск",
  "Брянск",
  "Иваново",
  "Севастополь",
  "Белгород",
  "Сургут",
  "Владимир",
  "Нижний Тагил",
  "Архангельск",
  "Чита",
  "Симферополь",
  "Курган",
  "Орёл",
  "Вологда",
  "Грозный",
  "Якутск",
  "Тамбов",
  "Петрозаводск",
  "Стерлитамак",
  "Кострома",
  "Нижневартовск",
  "Йошкар-Ола",
  "Новороссийск",
  "Дзержинск",
  "Нальчик",
  "Сыктывкар",
  "Шахты",
  "Братск",
  "Орск",
  "Энгельс",
  "Благовещенск",
  "Ангарск",
  "Подольск",
  "Старый Оскол",
  "Великий Новгород",
  "Коломна",
  "Псков",
  "Южно-Сахалинск",
  "Прокопьевск",
  "Бийск",
  "Балаково",
  "Рыбинск",
  "Арзамас",
  "Химки",
  "Северодвинск",
  "Петропавловск-Камчатский",
  "Копейск",
  "Норильск",
  "Мытищи",
  "Альметьевск",
  "Красногорск",
  "Пятигорск",
  "Дмитровград",
  "Березники",
  "Кызыл",
  "Салават",
  "Нефтекамск",
];

const cityInput = document.getElementById("city");
const autocompleteItems = document.getElementById("autocomplete-items");

function showAutocomplete(value) {
  autocompleteItems.innerHTML = "";

  if (!value) {
    return false;
  }

  const valueUpper = value.toUpperCase();

  cities.forEach((city) => {
    if (city.toUpperCase().includes(valueUpper)) {
      const item = document.createElement("div");
      item.textContent = city;
      item.classList.add("autocomplete-item");

      item.addEventListener("click", function () {
        cityInput.value = this.textContent;
        autocompleteItems.innerHTML = "";
      });

      autocompleteItems.appendChild(item);
    }
  });
}

cityInput.addEventListener("input", function () {
  showAutocomplete(this.value);
});

document.addEventListener("click", function (event) {
  if (
    !cityInput.contains(event.target) &&
    !autocompleteItems.contains(event.target)
  ) {
    autocompleteItems.innerHTML = "";
  }
});
