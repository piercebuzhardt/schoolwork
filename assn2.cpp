//
//		          Programming Assignment #2 
//
//			        Victor Zordan
//		
//		
//
/***************************************************************************/
//Pierce Buzhardt
                                                   /* Include needed files */
#include <GL/gl.h>
#include <GL/glu.h>
#include <stdlib.h>
#include <stdio.h>
#include <math.h>
#include <string.h>
#include <algorithm>
#include <iostream>
#include <vector>

#include <GL/glut.h>   // The GL Utility Toolkit (Glut) Header

#define WIDTH 500
#define HEIGHT 500
#define PI 3.14159265358979323846

int x_last,y_last;
int x_prev, y_prev;
enum transformation {Translation, Rotation, Scale};
enum transformation state = Translation;
enum view {Orthogonal, Perspective};
enum view currentView = Orthogonal;

struct Vertex{
    float x;
    float y;
    float z;
};

struct Face{
    int vertex1;
    int vertex2;
    int vertex3;
	int vertex4;
	bool four;
};

std::vector<struct Vertex> vertices;
std::vector<struct Face> faces;

int cameraX = WIDTH / 2;
int cameraY = HEIGHT / 2;
float zoom = 1;
float zoomLevel = 1.25;

float translationRate = 15;

float rotationRate = 15;

float scaleRate = 1.05;

float xCen = 0, yCen = 0, zCen = 0;

float zDiff;

float perZoom;

/***************************************************************************/

void init_window()
    /* Clear the image area, and set up the coordinate system */
{
    /* Clear the window */
    glClearColor(0.0,0.0,0.0,0.0);
	glShadeModel(GL_SMOOTH);
    glOrtho(0,WIDTH,0,HEIGHT,-1.0,1.0);
}

/***************************************************************************/

void write_pixel(int x, int y, double intensity)
/* Turn on the pixel found at x,y */
{
    glColor3f (intensity, intensity, intensity);      
    glBegin(GL_POINTS);
    glVertex3i( x, y, 0);
    glEnd();	
}

//***************************************************************************/
void draw_line(int x1, int y1, int x2, int y2){
    int x, y;
    //prevent divide by zero error
    if(x1 != x2){
        if(x1 > x2){
            std::swap(x1,x2);
            std::swap(y1,y2);
        }
        double m;
        int a, b;
        double d;
        x = x1;
        y = y1;
        a = y2 - y1; //change of y
        b = x2 - x1; //change of x
        m = (double)a / b;
        write_pixel(x, y, 1.0);
        if(m < 1 && m >= 0){
            //positive low slope
            d = a - (b / 2);
            for(; x <= x2; x++){
                if(d < 0){
                    d = d + a;
                }else{
                    y++;
                    d = d + (a - b);
                }
                write_pixel(x, y, 1.0);
            }
        }else if(m >= 1){
            //positive high slope
            d = b - (a /2);
            for(; y <= y2; y++){
                if(d < 0){
                    d = d + b;
                }else{
                    x++;
                    d = d + (b - a);
                }
                write_pixel(x, y, 1.0);
            }
        }else if(m < 0 && m >= -1){
            //negative low slope
            d = a + (b / 2);
            for(; x <= x2; x++){
                if(d < 0){
                    d = d - a;
                }else{
                    y--;
                    d = d - a - b;
                }
                write_pixel(x, y, 1.0);
            }
        }else{
            //negative high slope
            d = b + (a / 2);
            for(; y >= y2; y--){
                if(d < 0){
                    d = d + b;
                }else{
                    x++;
                    d = d + b + a;
                }
                write_pixel(x, y, 1.0);
            }
        }
    }else{
        //vertical
        if(y1 > y2){
            std::swap(y1,y2);
            std::swap(x1,x2);
        }
        x = x1;
        y = y1;
        for(;y <= y2; y++)
            write_pixel(x, y, 1.0);
    }
}


//displays the wireframe of the model by iterating through faces, taking the index
//of each vertex of a face, then drawing lines between each vertex of a face
//then centered into the middle of the screen
void displayModel(){
    if(currentView == Orthogonal){
		for(unsigned int i = 0; i < faces.size(); i++){
			draw_line((int)((vertices[faces[i].vertex1].x) * zoom + cameraX + .5),
					  (int)((vertices[faces[i].vertex1].y) * zoom + cameraY + .5),
					  (int)((vertices[faces[i].vertex2].x) * zoom + cameraX + .5),
					  (int)((vertices[faces[i].vertex2].y) * zoom + cameraY + .5));
			draw_line((int)((vertices[faces[i].vertex2].x) * zoom + cameraX + .5),
					  (int)((vertices[faces[i].vertex2].y) * zoom + cameraY + .5),
					  (int)((vertices[faces[i].vertex3].x) * zoom + cameraX + .5),
					  (int)((vertices[faces[i].vertex3].y) * zoom + cameraY + .5));
		    if(!faces[i].four){
				draw_line((int)((vertices[faces[i].vertex3].x) * zoom + cameraX + .5),
					      (int)((vertices[faces[i].vertex3].y) * zoom + cameraY + .5),
					      (int)((vertices[faces[i].vertex1].x) * zoom + cameraX + .5),
					      (int)((vertices[faces[i].vertex1].y) * zoom + cameraY + .5));
			}else{
				draw_line((int)((vertices[faces[i].vertex3].x) * zoom + cameraX + .5),
					      (int)((vertices[faces[i].vertex3].y) * zoom + cameraY + .5),
					      (int)((vertices[faces[i].vertex4].x) * zoom + cameraX + .5),
					      (int)((vertices[faces[i].vertex4].y) * zoom + cameraY + .5));
				draw_line((int)((vertices[faces[i].vertex4].x) * zoom + cameraX + .5),
					      (int)((vertices[faces[i].vertex4].y) * zoom + cameraY + .5),
					      (int)((vertices[faces[i].vertex1].x) * zoom + cameraX + .5),
					      (int)((vertices[faces[i].vertex1].y) * zoom + cameraY + .5));
			}
		}
	}else{
		for(unsigned int i = 0; i < faces.size(); i++){
			draw_line((int)((vertices[faces[i].vertex1].x) / -(vertices[faces[i].vertex1].z -  1.5 * zDiff) * perZoom + cameraX + .5),
					  (int)((vertices[faces[i].vertex1].y) / -(vertices[faces[i].vertex1].z -  1.5 * zDiff) * perZoom + cameraY + .5),
					  (int)((vertices[faces[i].vertex2].x) / -(vertices[faces[i].vertex2].z -  1.5 * zDiff) * perZoom + cameraX + .5),
					  (int)((vertices[faces[i].vertex2].y) / -(vertices[faces[i].vertex2].z -  1.5 * zDiff) * perZoom + cameraY + .5));
			draw_line((int)((vertices[faces[i].vertex2].x) / -(vertices[faces[i].vertex2].z -  1.5 * zDiff) * perZoom + cameraX + .5),
					  (int)((vertices[faces[i].vertex2].y) / -(vertices[faces[i].vertex2].z -  1.5 * zDiff) * perZoom + cameraY + .5),
					  (int)((vertices[faces[i].vertex3].x) / -(vertices[faces[i].vertex3].z -  1.5 * zDiff) * perZoom + cameraX + .5),
					  (int)((vertices[faces[i].vertex3].y) / -(vertices[faces[i].vertex3].z -  1.5 * zDiff) * perZoom + cameraY + .5));
			if(!faces[i].four){
				draw_line((int)((vertices[faces[i].vertex3].x) / -(vertices[faces[i].vertex3].z -  1.5 * zDiff) * perZoom + cameraX + .5),
						  (int)((vertices[faces[i].vertex3].y) / -(vertices[faces[i].vertex3].z -  1.5 * zDiff) * perZoom + cameraY + .5),
						  (int)((vertices[faces[i].vertex1].x) / -(vertices[faces[i].vertex1].z -  1.5 * zDiff) * perZoom + cameraX + .5),
						  (int)((vertices[faces[i].vertex1].y) / -(vertices[faces[i].vertex1].z -  1.5 * zDiff) * perZoom + cameraY + .5));
			}else{
				draw_line((int)((vertices[faces[i].vertex3].x) / -(vertices[faces[i].vertex3].z -  1.5 * zDiff) * perZoom + cameraX + .5),
						  (int)((vertices[faces[i].vertex3].y) / -(vertices[faces[i].vertex3].z -  1.5 * zDiff) * perZoom + cameraY + .5),
						  (int)((vertices[faces[i].vertex4].x) / -(vertices[faces[i].vertex4].z -  1.5 * zDiff) * perZoom + cameraX + .5),
						  (int)((vertices[faces[i].vertex4].y) / -(vertices[faces[i].vertex4].z -  1.5 * zDiff) * perZoom + cameraY + .5));
				draw_line((int)((vertices[faces[i].vertex4].x) / -(vertices[faces[i].vertex4].z -  1.5 * zDiff) * perZoom + cameraX + .5),
						  (int)((vertices[faces[i].vertex4].y) / -(vertices[faces[i].vertex4].z -  1.5 * zDiff) * perZoom + cameraY + .5),
						  (int)((vertices[faces[i].vertex1].x) / -(vertices[faces[i].vertex1].z -  1.5 * zDiff) * perZoom + cameraX + .5),
						  (int)((vertices[faces[i].vertex1].y) / -(vertices[faces[i].vertex1].z -  1.5 * zDiff) * perZoom + cameraY + .5));
			}
		}
	}
}

//angle in degrees, but sin/cos takes radians, so multiply angle by PI/180
//rotates around the x axis.
void rotateX(float angle){
    for(unsigned int i = 0; i < vertices.size(); i++){
        float y = vertices[i].y, z = vertices[i].z;
		vertices[i].y = (y - yCen) * cos(angle * PI / 180) - (z - zCen) * sin(angle * PI / 180) + yCen;
		vertices[i].z = (y - yCen) * sin(angle * PI / 180) + (z - zCen) * cos(angle * PI / 180) + zCen;
    }
}

//rotates around the y axis by [angle] degrees
void rotateY(float angle){
    for(unsigned int i = 0; i < vertices.size(); i++){
		float x = vertices[i].x, z = vertices[i].z;
		vertices[i].x = (x - xCen) * cos(angle * PI / 180) + (z - zCen) * sin(angle * PI / 180) + xCen;
		vertices[i].z = -1 * (x - xCen) * sin(angle * PI / 180) + (z - zCen) * cos(angle * PI / 180) + zCen;
	}
}

//scales the vertices by [rate] centered on the origin
void scaleX(float rate){
	if(rate >= 0){
		for(unsigned int i = 0; i < vertices.size(); i++){
			vertices[i].x = rate * (vertices[i].x - xCen) + xCen;
		}
		//xCen = rate * xCen;
	}else{
		for(unsigned int i = 0; i < vertices.size(); i++){
			vertices[i].x = (1 / -rate) * (vertices[i].x - xCen) + xCen;
		}
		//xCen = xCen / rate;
	}

}

void scaleY(float rate){
	if(rate >= 0){
		for(unsigned int i = 0; i < vertices.size(); i++){
			vertices[i].y = rate * (vertices[i].y - yCen) + yCen;
		}
		//yCen = rate * yCen;
	}else{
		for(unsigned int i = 0; i < vertices.size(); i++){
			vertices[i].y = (1 / -rate) * (vertices[i].y - yCen) + yCen;
		}
		//yCen = yCen / rate;
	}
}

//moves vertices by [displace] amount
void translateX(float displace){
	for(unsigned int i = 0; i < vertices.size(); i++){
		vertices[i].x = vertices[i].x + displace;
	}
	xCen = xCen + displace;
}

void translateY(float displace){
	for(unsigned int i = 0; i < vertices.size(); i++){
		vertices[i].y = vertices[i].y + displace;
	}
	yCen = yCen + displace;
}


void display ( void )   // Create The Display Function
{

    glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);	      // Clear Screen 

	// CALL YOUR CODE HERE

    displayModel();
    glutSwapBuffers();                                      // Draw Frame Buffer 
}

/***************************************************************************/
void mouse(int button, int state, int x, int y)
{
/* This function I finessed a bit, the value of the printed x,y should
   match the screen, also it remembers where the old value was to avoid multiple
   readings from the same mouse click.  This can cause problems when trying to
   start a line or curve where the last one ended */
    static int oldx = 0;
    static int oldy = 0;
	int mag;
	y *= -1;  //align y with mouse
	y += 500; //ignore 
	
	mag = (oldx - x)*(oldx - x) + (oldy - y)*(oldy - y);
	
	if (mag > 20) {
		printf(" x,y is (%d,%d)\n", x,y);
    }
	
    oldx = x;
	oldy = y;
	x_last = x;
	y_last = y;
	
	if(button == 3 || button == 4){
		if(button == 3){
			zoom = zoom * zoomLevel;
			perZoom = perZoom * zoomLevel;
		}else{
			zoom = zoom / zoomLevel;
			perZoom = perZoom / zoomLevel;
		}
	}
}
 
/***************************************************************************/
void keyboard ( unsigned char key, int x, int y )  // Create Keyboard Function
{

	switch ( key ) {
		case 27:              // When Escape Is Pressed...
			exit ( 0 );   // Exit The Program
			break;        
	    case '1':             // stub for new screen
		    printf("New screen\n");
			break;
        //change transformation state via key
        case 't':
            state = Translation;
            break;
        case 'r':
            state = Rotation;
            break;
        case 'e':
            state = Scale;
            break;
        //affects the vertices depending on transformation state and key pressed
		case 'w':
            if(state == Translation){
                translateY(translationRate);
            }else if(state == Rotation){
                rotateX(rotationRate);
            }else if(state == Scale){
                scaleY(scaleRate);
            }
            break;
        case 'a':
            if(state == Translation){
                translateX(-translationRate);
            }else if(state == Rotation){
                rotateY(-rotationRate);
            }else if(state == Scale){
                scaleX(-scaleRate);
            }
            break;
        case 's':
            if(state == Translation){
                translateY(-translationRate);
            }else if(state == Rotation){
                rotateX(-rotationRate);
            }else if(state == Scale){
                scaleY(-scaleRate);
            }
            break;
        case 'd':
            if(state == Translation){
                translateX(translationRate);
            }else if(state == Rotation){
                rotateY(rotationRate);
            }else if(state == Scale){
                scaleX(scaleRate);
            }
            break;
        case 'v':
            if(currentView == Orthogonal){
				currentView = Perspective;
			}else{
				currentView = Orthogonal;
			}
            break;
		default:       
			break;
	}
}
/***************************************************************************/


int main (int argc, char *argv[])
{
/* This main function sets up the main loop of the program and continues the
   loop until the end of the data is reached.  Then the window can be closed
   using the escape key.						  */
	glutInit            ( &argc, argv ); 
    glutInitDisplayMode ( GLUT_RGB | GLUT_DOUBLE | GLUT_DEPTH ); 
	glutInitWindowSize  ( 500,500 ); 
	glutCreateWindow    ( "Computer Graphics" ); 
	glutDisplayFunc     ( display );  
	glutIdleFunc	    ( display );
	glutMouseFunc       ( mouse );
	glutKeyboardFunc    ( keyboard );

	FILE * model;
    if(argc > 1){
        model = fopen(argv[1], "r");
        if(model == NULL){
            std::cout << "Unable to open file: " << argv[1] << std::endl;
            return -1;
        }
        while(1){
            char lineHeader[128];
            int res = fscanf(model, "%s", lineHeader);
            if(res == EOF)
                break;
            if(strcmp(lineHeader, "v") == 0){
                struct Vertex vertex;
                fscanf(model, "%f %f %f\n", &vertex.x, &vertex.y, &vertex.z);

                vertices.push_back(vertex);
            }else if(strcmp(lineHeader, "f") == 0){
                struct Face face;
                fscanf(model, "%d/%*d/%*d %d/%*d/%*d %d/%*d/%*d", &face.vertex1, &face.vertex2, &face.vertex3);
                face.vertex1 = face.vertex1 - 1;
                face.vertex2 = face.vertex2 - 1;
                face.vertex3 = face.vertex3 - 1;
                int c = fgetc(model);
				ungetc(c, model);
                if(c >= 48 && c <= 57){
					fscanf(model, "%d/%*d/%*d\n", &face.vertex4);
					face.vertex4 = face.vertex4 - 1;
					face.four = true;
				}else{
					fscanf(model, "\n");
					face.four = false;
				}
                faces.push_back(face);
            }
        }
        
		//centers the model and finds zoom factor

		float xMin, xMax, yMin, yMax, zMin, zMax;
		for(unsigned int i = 0; i < vertices.size(); i++){
			if(i == 0){
				xMin = vertices[i].x;
				xMax = vertices[i].x;
				yMin = vertices[i].y;
				yMax = vertices[i].y;
				zMin = vertices[i].z;
				zMax = vertices[i].z;
			}else{
				if(vertices[i].x < xMin){
					xMin = vertices[i].x;
				}
				if(vertices[i].x > xMax){
					xMax = vertices[i].x;
				}
				if(vertices[i].y < yMin){
					yMin = vertices[i].y;
				}
				if(vertices[i].y > yMax){
					yMax = vertices[i].y;
				}
				if(vertices[i].z < zMin){
					zMin = vertices[i].z;
				}
				if(vertices[i].z > zMax){
					zMax = vertices[i].z;
				}
			}
			xCen = xCen + vertices[i].x;
			yCen = yCen + vertices[i].y;
			zCen = zCen + vertices[i].z;
			
		}
		zDiff = zMax - zMin;
		xCen = xCen / vertices.size();
		yCen = yCen / vertices.size();
		zCen = zCen / vertices.size();
		
		//updates the vertices to recenter and fit the window size
		for(unsigned int i = 0; i < vertices.size(); i++){
			vertices[i].x = vertices[i].x - xCen;
			vertices[i].y = vertices[i].y - yCen;
			vertices[i].z = vertices[i].z - zCen;
			//Pierce, consider updating zoom instead of modifying the vertex values
		/*	if((xMax - xMin) > .8 * WIDTH){
				vertices[i].x = (int)(.8 * WIDTH / (xMax - xMin) * vertices[i].x);
				vertices[i].y = (int)(.8 * WIDTH / (xMax - xMin) * vertices[i].y);
				vertices[i].z = (int)(.8 * WIDTH / (xMax - xMin) * vertices[i].z);
			}else if(xMax - xMin < .3 * WIDTH){
				vertices[i].x = (int)((xMax - xMin) / (.3 * WIDTH) * vertices[i].x);
				vertices[i].y = (int)((xMax - xMin) / (.3 * WIDTH) * vertices[i].y);
				vertices[i].z = (int)((xMax - xMin) / (.3 * WIDTH) * vertices[i].z);
			}
			if(yMax - yMin > .8 * HEIGHT){
				vertices[i].x = (int)(.8 * HEIGHT / (yMax - yMin) * vertices[i].x);
				vertices[i].y = (int)(.8 * HEIGHT / (yMax - yMin) * vertices[i].y);
				vertices[i].z = (int)(.8 * HEIGHT / (yMax - yMin) * vertices[i].z);
			}else if(yMax - yMin < .3 * HEIGHT){
				vertices[i].x = (int)((yMax - yMin) / (.3 * HEIGHT) * vertices[i].x);
				vertices[i].y = (int)((yMax - yMin) / (.3 * HEIGHT) * vertices[i].y);
				vertices[i].z = (int)((yMax - yMin) / (.3 * HEIGHT) * vertices[i].z);
			}*/
		}
		if((xMax - xMin) * zoom > .8 * WIDTH){
			zoom = (.8 * WIDTH / (xMax - xMin) * zoom);
		}else if((xMax - xMin) * zoom < .3 * WIDTH){
			zoom = ((xMax - xMin) / (.3 * WIDTH) * zoom);
		}
		if((yMax - yMin) * zoom > .8 * HEIGHT){
			zoom = (.8 * HEIGHT / (yMax - yMin) * zoom);
		}else if((yMax - yMin) * zoom < .3 * HEIGHT){
			zoom = ((yMax - yMin) / (.3 * HEIGHT) * zoom);
		}
		
		perZoom = zoom * zDiff;
		
		xCen = xCen - xCen;
		yCen = yCen - yCen;
		zCen = zCen - zCen;
		
    }else{
        std::cout << "No command line argument provided." << std::endl;
        return -1;
    }
    
    init_window();				             //create_window
	
	glutMainLoop        ( );                 // Initialize The Main Loop

}