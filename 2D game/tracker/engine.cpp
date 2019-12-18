#include <iostream>
#include <algorithm>
#include <sstream>
#include <string>
#include <random>
#include <iomanip>
#include "hud.h"
#include "sprite.h"
#include "multisprite.h"
#include "gameData.h"
#include "engine.h"
#include "player.h"
#include "frameGenerator.h"
#include "twowaymultisprite.h"
#include "collisionStrategy.h"
#include "smartMultiSprite.h"
#include "sound.h"

int Engine::poolSize(){
	return inactiveSprites.size();
}

Engine::~Engine() { 
  for(auto& s : sprites){
	  delete s;
  }
  for(auto& s : inactiveSprites){
	  delete s;
  }
  for(auto& s : backgroundSprites){
	  delete s;
  }
  delete player;
  delete strategy;
  std::cout<<"Terminating program"<<std::endl;
}

Engine::Engine() :
  rc( &RenderContext::getInstance() ),
  io( IoMod::getInstance() ),
  clock( Clock::getInstance() ),
  renderer( rc->getRenderer() ),
  layer1("layer1", Gamedata::getInstance().getXmlInt("layer1/factor") ),
  layer2("layer2", Gamedata::getInstance().getXmlInt("layer2/factor") ),
  layer3("layer3", Gamedata::getInstance().getXmlInt("layer3/factor") ),
  viewport( Viewport::getInstance() ),
  sprites(),
  inactiveSprites(),
  backgroundSprites(),
  currentSprite(0),
  makeVideo( false ),
  hud(Hud::getInstance()),
  player(new Player("Player")),
  strategy(new PerPixelCollisionStrategy),
  collision(false),
  sound(),
  fruitTotal(Gamedata::getInstance().getXmlInt("numberOfFruit")),
  godMode(false),
  poolHud(true),
  fps(true)
{  
  Viewport::getInstance().setObjectToTrack(player);
  int n = Gamedata::getInstance().getXmlInt("maxNumberOfFruitOnScreen");
  int m = Gamedata::getInstance().getXmlInt("numberOfGordo");
  sprites.reserve(n + m);
  inactiveSprites.reserve(n);
  
  Vector2f pos = player->getPosition();
  int w = player->getScaledWidth();
  int h = player->getScaledHeight();
  bool st = player->isDamagable();
  int i = 0;
  for(; i < n; i++){
	  sprites.emplace_back(new SmartMultiSprite("Fruit", pos, w, h, st));
	  player->attach(static_cast<SmartMultiSprite*>(sprites[i]));
  }
  for(; i < n+m; i++){
	  sprites.emplace_back(new SmartMultiSprite("Gordo", pos, w, h, st));
	  player->attach(static_cast<SmartMultiSprite*>(sprites[i]));
  }
  
  n = Gamedata::getInstance().getXmlInt("numberOfBronto");
  m = Gamedata::getInstance().getXmlInt("numberOfScarfy");
  
  backgroundSprites.reserve(n + m);
  i = 0;
  for(; i < n; i++){
	  backgroundSprites.emplace_back(new TwoWayMultiSprite("Bronto"));
	  backgroundSprites[i]->setVelocityX(Gamedata::getInstance().getXmlInt("Bronto/speedX") + Gamedata::getInstance().getRandFloat(-10, 10));
	  backgroundSprites[i]->setVelocityY(Gamedata::getInstance().getXmlInt("Bronto/speedY") + Gamedata::getInstance().getRandFloat(-10, 10));
  }
  for(; i < n+m; i++){
	  backgroundSprites.emplace_back(new TwoWayMultiSprite("Scarfy"));
	  backgroundSprites[i]->setVelocityX(Gamedata::getInstance().getXmlInt("Scarfy/speedX") + Gamedata::getInstance().getRandFloat(-10, 10));
	  backgroundSprites[i]->setVelocityY(Gamedata::getInstance().getXmlInt("Scarfy/speedY") + Gamedata::getInstance().getRandFloat(-10, 10));
  }
  
  std::cout << "Loading complete" << std::endl;
}

void Engine::draw() const {
  layer3.draw();
  layer2.draw();
  for(const Drawable* sprite : backgroundSprites){
	  sprite->draw();
  }
  layer1.draw();

  /*for(int i = 0; i < static_cast<int>(sprites.size()); i++){
    sprites[i]->draw();
  }*/
  for ( const Drawable* sprite : sprites ) {
    sprite->draw();
  }
  player->draw();
  io.writeText("Pierce Buzhardt", 30, 400);
  if(fps){
	io.writeText("FPS: " + std::to_string(clock.getFps()),
    Gamedata::getInstance().getXmlInt("hud/loc/x")+10, 
    Gamedata::getInstance().getXmlInt("hud/loc/y")+10);
  }
  viewport.draw();
  if(hud.isVisible())
	hud.showHud();
  if(poolHud){
	SDL_SetRenderDrawBlendMode(renderer, SDL_BLENDMODE_BLEND);
    SDL_SetRenderDrawColor( renderer, 255, 255, 255, 255/2);
    SDL_Rect r;
    r.x = 440;
    r.y = 0;
    r.w = 200;
    r.h = 100;
    SDL_RenderFillRect(renderer, &r);
	
	SDL_SetRenderDrawColor(renderer, 0, 0, 0, 255);
	io.writeText("Sprites: " + std::to_string(sprites.size() - Gamedata::getInstance().getXmlInt("numberOfGordo")), 450, 10);
	io.writeText("Inactive: " + std::to_string(inactiveSprites.size()), 450, 40);
	io.writeText("F2:toggle", 450, 70);
  }
  if(player->getDead()){
	  io.writeText("You are out of lives.", Gamedata::getInstance().getXmlInt("view/width") / 2 - 50, Gamedata::getInstance().getXmlInt("view/height") / 2);
  }
  if(fruitTotal <= 0){
	  io.writeText("Kirby's full now. You win.", Gamedata::getInstance().getXmlInt("view/width") / 2 - 100, Gamedata::getInstance().getXmlInt("view/height") / 2);
	  io.writeText(" R:restart; Q:quit", Gamedata::getInstance().getXmlInt("view/width") / 2 - 100, Gamedata::getInstance().getXmlInt("view/height") / 2 + 30);
  }
  
  io.writeText("God mode: " + std::to_string(godMode), 440, 390);
  io.writeText("Invicibilty Time: " + std::to_string(player->getInvincibleT()), 440, 420);
  io.writeText("Fruit left: " + std::to_string(fruitTotal), 440, 450);
  SDL_RenderPresent(renderer);
}

void Engine::checkForCollisions(){
	auto it = sprites.begin();
	while(it != sprites.end() && !player->isExplosion() && player->isDamagable()){
		if(strategy->execute(*player, **it) && !static_cast<SmartMultiSprite*>(*it)->isExploding()){
			SmartMultiSprite* doa = static_cast<SmartMultiSprite*>(*it);
			if(doa->getName() == "Fruit" && !doa->isExploding()){
//				player->detach(doa);
//				delete doa;
				doa->explode();
				fruitTotal--;
				SDL_RenderClear(renderer);
			}else if(doa->getName() == "Gordo" && !godMode && player->isDamagable()){
				player->damage();
				fruitTotal++;
				if(!inactiveSprites.empty()){
					auto it = inactiveSprites.begin();
					SmartMultiSprite* doa = static_cast<SmartMultiSprite*>(*it);
					sprites.emplace_back(doa);
					inactiveSprites.erase(it);
				}
				doa->reset();
				SDL_RenderClear(renderer);
			}else{
				doa->reset();
				doa->inverse();
			}
		}
		++it;
	}
}

void Engine::update(Uint32 ticks) {

/*  for(int i = 0; i < static_cast<int>(sprites.size()); i++){
    sprites[i]->update(ticks);
  }*/
  if(!player->isExplosion() && !player->getDead())
	checkForCollisions();

  if(fruitTotal + Gamedata::getInstance().getXmlInt("numberOfGordo") < (signed)sprites.size() && (signed)sprites.size() > Gamedata::getInstance().getXmlInt("numberOfGordo")){
	auto it = sprites.begin();
	while(it != sprites.end()){
	if(static_cast<SmartMultiSprite*>(*it)->getName() == "Fruit" && !static_cast<SmartMultiSprite*>(*it)->isExploding()){
		SmartMultiSprite* doa = static_cast<SmartMultiSprite*>(*it);
		inactiveSprites.emplace_back(doa);
		sprites.erase(it);
		break;
		}
	++it;
	}
  }
  
  for(Drawable* sprite: sprites){
	  sprite->update(ticks);
  }
  
for(Drawable* sprite: backgroundSprites){
	sprite->update(ticks);
}
  
  layer3.update();  
  layer2.update();
  layer1.update();
  player->update(ticks);
  hud.update();
  viewport.update(); // always update viewport last
}
/*
void Engine::switchSprite(){
  ++currentSprite;
  currentSprite = currentSprite % sprites.size();
  Viewport::getInstance().setObjectToTrack(sprites[currentSprite]);
}
*/
bool Engine::play() {
  SDL_Event event;
  const Uint8* keystate;
  bool done = false;
  bool restart = false;
  Uint32 ticks = clock.getElapsedTicks();
  FrameGenerator frameGen;

  while ( !done ) {
    // The next loop polls for events, guarding against key bounce:
    while ( SDL_PollEvent(&event) ) {
      keystate = SDL_GetKeyboardState(NULL);
      if (event.type ==  SDL_QUIT) { done = true; break; }
      if(event.type == SDL_KEYDOWN) {
        if (keystate[SDL_SCANCODE_ESCAPE] || keystate[SDL_SCANCODE_Q]) {
          done = true;
          break;
        }
        if ( keystate[SDL_SCANCODE_P] ) {
          if ( clock.isPaused() ) clock.unpause();
          else clock.pause();
        }
        if (keystate[SDL_SCANCODE_F4] && !makeVideo) {
          std::cout << "Initiating frame capture" << std::endl;
          makeVideo = true;
        }
        else if (keystate[SDL_SCANCODE_F4] && makeVideo) {
          std::cout << "Terminating frame capture" << std::endl;
          makeVideo = false;
        }
		
		if(keystate[SDL_SCANCODE_F1] && !hud.isVisible()){
			hud.openHud();
		}else if (keystate[SDL_SCANCODE_F1] && hud.isVisible()){
			hud.closeHud();
		}
		
		if(keystate[SDL_SCANCODE_F2]){
			poolHud = !poolHud;
			
		}
		if(keystate[SDL_SCANCODE_F3]){
			fps = !fps;
		}
		
	    if(keystate[SDL_SCANCODE_R]){
		  done = true;
		  restart = true;
		  break;
	    }
	    if(keystate[SDL_SCANCODE_G]){
		  godMode = !godMode;
	    }
		
		if(keystate[SDL_SCANCODE_K] && player->isPoofed()){
			player->puff();
		}
		
			  
		if(keystate[SDL_SCANCODE_M]){
		    sound.toggleMusic();
	    }
      }
    }

    // In this section of the event loop we allow key bounce:

    ticks = clock.getElapsedTicks();
    if ( ticks > 0 ) {
      clock.incrFrame();
	  if(keystate[SDL_SCANCODE_A]){
		  player->left();
	  }
      if(keystate[SDL_SCANCODE_K] && player->isPoofed()){
		 sound[0]; 
	  }
	  if(keystate[SDL_SCANCODE_D]){
		  player->right();
	  }
	  if(keystate[SDL_SCANCODE_W]){
		  player->up();
	  }
	  if(keystate[SDL_SCANCODE_S]){
		  player->down();
	  }
	  if(keystate[SDL_SCANCODE_J]){
		  player->jump();
		  sound[2];
	  }

	  if(!hud.isUpdated() && !hud.isVisible())
	      SDL_RenderClear(renderer);
	  
	  if(fruitTotal <= 0)
		  godMode = true;
	  
      draw();
      update(ticks);

      if ( makeVideo ) {
        frameGen.makeFrame();
      }
    }
  }
  return restart;
}
