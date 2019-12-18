#ifndef ENGINE__H
#define ENGINE__H

#include <vector>
#include <SDL.h>
#include "ioMod.h"
#include "renderContext.h"
#include "clock.h"
#include "world.h"
#include "viewport.h"
#include "hud.h"
#include "player.h"
#include "sound.h"
#include "multisprite.h"

class CollisionStrategy;
class SmartMultiSprite;
class Player;

class Engine {
public:
  Engine ();
  ~Engine ();
  bool play();
//  void switchSprite();

private:
  const RenderContext* rc;
  const IoMod& io;
  Clock& clock;
 
  SDL_Renderer * const renderer;

  //MultiSprite layer1;
  World layer1;
  World layer2;
  World layer3;
  Viewport& viewport;

  std::vector<Drawable*> sprites;
  std::vector<Drawable*> inactiveSprites;
  std::vector<Drawable*> backgroundSprites;
  
  int poolSize();
  
  int currentSprite;

  bool makeVideo;
  
  Hud& hud;
  
  Player* player;
  CollisionStrategy* strategy;
  bool collision;
  
  SDLSound sound;
  
  int fruitTotal;
  
  bool godMode;
  bool poolHud;
  bool fps;
  

  void draw() const;
  void update(Uint32);

  Engine(const Engine&);
  Engine& operator=(const Engine&);
  void printScales() const;
  void checkForCollisions();
  
};
#endif