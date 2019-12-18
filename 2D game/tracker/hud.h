#ifndef HUD__H
#define HUD__H

#include <string>
#include <SDL.h>
#include <iostream>
#include "ioMod.h"
#include "clock.h"
#include "renderContext.h"

class Hud {
public:
	static Hud& getInstance();
	~Hud();
	void showHud();
	void openHud();
	void closeHud();
	bool isVisible();
	bool isUpdated();
	void update();
private:
	Hud();
	Hud(const Hud&);
	Hud& operator=(const Hud&);
	SDL_Color* textColorPtr;
	SDL_Color* hudColorPtr;
	
	bool visible;
	bool updated;
	
	SDL_Color black = {0, 0, 0, 255};
	SDL_Color bg = {255, 255, 255, 255/2};
	SDL_Color clear = {0, 0, 0, 0};
	
	const IoMod& io;
	Clock& clock;
	SDL_Renderer* renderer;
};
#endif