// Pierce Buzhardt        Data-Driven Object oriented Game Construction
#include "engine.h"

int main(int, char*[]) {
   try {
	  bool cont = true;
	  while(cont){
		Engine engine;
		cont = engine.play();
	  }
   }
   catch (const string& msg) { std::cout << msg << std::endl; }
   catch (...) {
      std::cout << "Oops, someone threw an exception!" << std::endl;
   }
   return 0;
}
