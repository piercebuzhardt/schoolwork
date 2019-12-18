#include <SDL_image.h>
#include <SDL2/SDL.h>
#include "hud.h"
#include "gameData.h"
#include "renderContext.h"
#include "ioMod.h"
#include "clock.h"
#include "renderContext.h"

Hud& Hud::getInstance(){
	static Hud instance;
	return instance;
}

Hud::~Hud(){
}

Hud::Hud():
	textColorPtr(&black),
	hudColorPtr(&bg),
	visible(true),
	updated(true),
	io(IoMod::getInstance()),
	clock(Clock::getInstance()),
	renderer( RenderContext::getInstance().getRenderer() )
	{

}

void Hud::showHud(){
    SDL_SetRenderDrawBlendMode(renderer, SDL_BLENDMODE_BLEND);
    SDL_SetRenderDrawColor( renderer, hudColorPtr->r, hudColorPtr->b, hudColorPtr->g, hudColorPtr->a);
    SDL_Rect r;
    r.x = Gamedata::getInstance().getXmlInt("hud/loc/x");
    r.y = Gamedata::getInstance().getXmlInt("hud/loc/y");
    r.w = Gamedata::getInstance().getXmlInt("hud/loc/w");
    r.h = Gamedata::getInstance().getXmlInt("hud/loc/h");
    SDL_RenderFillRect(renderer, &r);
	
	SDL_SetRenderDrawColor(renderer, textColorPtr->r, textColorPtr->b, textColorPtr->g, textColorPtr->a);
	SDL_RenderDrawRect(renderer, &r);
	
	io.writeText("Move:wasd keys",
	Gamedata::getInstance().getXmlInt("hud/loc/x")+10,
	Gamedata::getInstance().getXmlInt("hud/loc/y")+40);
	
	io.writeText("P:pause; G:godmode",
	Gamedata::getInstance().getXmlInt("hud/loc/x")+10,
	Gamedata::getInstance().getXmlInt("hud/loc/y")+70);

	io.writeText("F1:toggle HUD; F3:toggle fps",
	Gamedata::getInstance().getXmlInt("hud/loc/x")+10,
	Gamedata::getInstance().getXmlInt("hud/loc/y")+100);
	
	io.writeText("J to inflate; K to deflate", 
	Gamedata::getInstance().getXmlInt("hud/loc/x")+10, 
	Gamedata::getInstance().getXmlInt("hud/loc/y")+130);
	
	io.writeText("Q:quit; R:restart", 
	Gamedata::getInstance().getXmlInt("hud/loc/x")+10,
	Gamedata::getInstance().getXmlInt("hud/loc/y")+160);

}

void Hud::openHud(){
	if(textColorPtr != &black)
		textColorPtr = &black;
	if(hudColorPtr != &bg)
		hudColorPtr = &bg;
	if(visible != true){
		visible = true;
		updated = false;
	}
}
void Hud::closeHud(){
	if(textColorPtr != &clear)
		textColorPtr = &clear;
	if(hudColorPtr != &clear)
		hudColorPtr = &clear;
	if(visible != false){
		visible = false;
		updated = false;
	}
}

bool Hud::isVisible(){
	return visible;
}

bool Hud::isUpdated(){
	bool temp = updated;
	updated = true;
	return temp;
}

void Hud::update(){
	updated = true;
}