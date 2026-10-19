
const int examButton = 18;
const int serviceButton = 19;

void setup() {
  Serial.begin(115200);

  pinMode(examButton, INPUT_PULLUP);
  pinMode(serviceButton, INPUT_PULLUP);

  Serial.println("MATS Queue System Test Ready!");
  Serial.println("Press a button to test.");
}

void loop() {
  if (digitalRead(examButton) == LOW) {
    Serial.println("Examination Payment button pressed!");
    delay(300);
  }
  
  if (digitalRead(serviceButton) == LOW) {
    Serial.println("Other Student Service button pressed!");
    delay(300);
  }
}
